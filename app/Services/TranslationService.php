<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 8,
            'http_errors' => false,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
                'Accept' => 'application/json, text/plain, */*',
            ],
        ]);
    }

    /**
     * Translate text to English if it is detected as non-English
     *
     * @param string $text
     * @return array{translated_text: string, source_language: string}|null
     */
    public function translateToEnglish(string $text): ?array
    {
        $trimmed = trim($text);
        if (mb_strlen($trimmed) < 6) {
            return null;
        }

        // Clean text for translation query (strip URLs if whole text is just a URL)
        $cleanTextForLangDetection = preg_replace('~https?://\S+~i', '', $trimmed);
        if (mb_strlen(trim($cleanTextForLangDetection)) < 4) {
            return null;
        }

        // 1. Try MyMemory API with autodetect
        try {
            $response = $this->client->get('https://api.mymemory.translated.net/get', [
                'query' => [
                    'q' => mb_substr($trimmed, 0, 800),
                    'langpair' => 'autodetect|en',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();
            $data = json_decode($body, true);

            if ($statusCode === 200 && is_array($data)) {
                $responseStatus = (int) ($data['responseStatus'] ?? 0);
                $translatedText = $data['responseData']['translatedText'] ?? null;
                $detectedLang = strtolower($data['responseData']['detectedLanguage'] ?? '');

                // If already English, MyMemory returns 403 or text "PLEASE SELECT TWO DISTINCT LANGUAGES"
                if (
                    $responseStatus === 200
                    && !empty($translatedText)
                    && stripos($translatedText, 'PLEASE SELECT TWO DISTINCT LANGUAGES') === false
                ) {
                    $translatedText = html_entity_decode($translatedText, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                    if ($detectedLang === 'en' || strtolower(trim($translatedText)) === strtolower(trim($trimmed))) {
                        return null;
                    }

                    if (empty($detectedLang) && !empty($data['matches'][0]['source'])) {
                        $detectedLang = strtolower(substr($data['matches'][0]['source'], 0, 2));
                    }

                    return [
                        'translated_text' => $translatedText,
                        'source_language' => !empty($detectedLang) ? $detectedLang : 'fr',
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::info('MyMemory Translation API error: ' . $e->getMessage());
        }

        return null;
    }
}
