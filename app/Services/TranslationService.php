<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    protected Client $client;
    protected string $contactEmail;

    public function __construct(?string $contactEmail = null)
    {
        $this->contactEmail = $contactEmail ?? config('mail.from.address', '7924@inbox.lv');
        if (empty($this->contactEmail) || !filter_var($this->contactEmail, FILTER_VALIDATE_EMAIL)) {
            $this->contactEmail = '7924@inbox.lv';
        }

        $this->client = new Client([
            'timeout' => 8,
            'http_errors' => false,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
                'Accept' => 'application/json, text/plain, */*',
            ],
        ]);
    }

    /**
     * Guess likely source language using linguistic indicators (French, German, Norwegian, etc.)
     */
    public function detectLanguageHeuristic(string $text): ?string
    {
        $clean = strtolower($text);

        // French indicators
        if (
            preg_match("/\b(s'ils|c'est|d'oslo|l'équipe|d'un|qu'il|qu'on|gagnent|demain|jette|dans|avec|pour|les|des|une|sont|cette|mais|nous|vous|ils|elles|très|après|avant|monde|première|dernière|championnat|relais|victoire|course|félicitations)\b/iu", $clean)
            || preg_match('/[éèêëàâäôöîïùûüçœ]/u', $clean)
        ) {
            return 'fr';
        }

        // German indicators
        if (
            preg_match("/\b(und|die|der|das|den|dem|des|ein|eine|einer|einem|einen|nicht|mit|auf|für|von|ist|im|aus|nach|über|dass|wird|werden|beim|beendet|gewinnt|rennen|gesamtweltcup)\b/iu", $clean)
            || preg_match('/[äöüß]/u', $clean)
        ) {
            return 'de';
        }

        // Norwegian / Swedish indicators
        if (
            preg_match("/\b(og|som|det|for|med|til|ikke|har|seg|han|hun|ble|etter|norge|svenska|norske|verdenscup|skiskytter|seier|løp|løpet)\b/iu", $clean)
            || preg_match('/[øæå]/u', $clean)
        ) {
            return 'no';
        }

        // Italian indicators
        if (preg_match("/\b(con|per|della|nella|delle|degli|sono|questo|questa|dopo|mondiale|vittoria|gara|coppa)\b/iu", $clean)) {
            return 'it';
        }

        return null;
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

        $heuristicLang = $this->detectLanguageHeuristic($cleanTextForLangDetection);

        // 1. Primary Engine: High-speed translation API (dict-chrome-ex / at)
        foreach (['dict-chrome-ex', 'at'] as $clientType) {
            try {
                $response = $this->client->get('https://translate.googleapis.com/translate_a/single', [
                    'query' => [
                        'client' => $clientType,
                        'sl' => 'auto',
                        'tl' => 'en',
                        'dt' => 't',
                        'q' => mb_substr($trimmed, 0, 1500),
                    ],
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
                    ],
                ]);

                if ($response->getStatusCode() === 200) {
                    $data = json_decode((string) $response->getBody(), true);
                    if (isset($data[0]) && is_array($data[0])) {
                        $translatedText = '';
                        foreach ($data[0] as $part) {
                            if (isset($part[0])) {
                                $translatedText .= $part[0];
                            }
                        }

                        $detectedLang = strtolower($data[2] ?? '');
                        if (!empty($translatedText)) {
                            $translatedText = html_entity_decode($translatedText, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                            // If detected as English and text didn't change significantly, it's native English
                            if ($detectedLang === 'en' && empty($heuristicLang)) {
                                return null;
                            }

                            if (strtolower(trim($translatedText)) === strtolower(trim($trimmed)) && empty($heuristicLang)) {
                                return null;
                            }

                            $srcLang = !empty($detectedLang) ? $detectedLang : ($heuristicLang ?: 'fr');

                            return [
                                'translated_text' => $translatedText,
                                'source_language' => $srcLang,
                            ];
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::info("Primary Translation API ({$clientType}) error: " . $e->getMessage());
            }
        }

        // 2. Secondary Engine: MyMemory API
        try {
            $langpair = ($heuristicLang && in_array($heuristicLang, ['fr', 'de', 'no', 'sv', 'it', 'ru', 'uk']))
                ? "{$heuristicLang}|en"
                : 'autodetect|en';

            $response = $this->client->get('https://api.mymemory.translated.net/get', [
                'query' => [
                    'q' => mb_substr($trimmed, 0, 800),
                    'langpair' => $langpair,
                    'de' => $this->contactEmail,
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                $data = json_decode((string) $response->getBody(), true);
                if (isset($data['responseData']['translatedText'])) {
                    $translatedText = html_entity_decode($data['responseData']['translatedText'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $detectedLang = strtolower($data['responseData']['detectedLanguage'] ?? '');

                    if (
                        !empty($translatedText)
                        && stripos($translatedText, 'MYMEMORY WARNING') === false
                        && stripos($translatedText, 'PLEASE SELECT TWO DISTINCT LANGUAGES') === false
                    ) {
                        if ($detectedLang === 'en' && empty($heuristicLang)) {
                            return null;
                        }

                        $srcLang = !empty($detectedLang) ? $detectedLang : ($heuristicLang ?: 'fr');

                        return [
                            'translated_text' => $translatedText,
                            'source_language' => $srcLang,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::info('MyMemory Translation API error: ' . $e->getMessage());
        }

        return null;
    }
}
