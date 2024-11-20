<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class BiathlonResultApi
{
    protected string $base = 'https://biathlonresults.com/modules/sportapi/api/';

    protected function get(string $path, array $payload = []): Response
    {
        return Http::get(
            url: sprintf('%s/%s', rtrim($this->base, '/'), ltrim($path, '/')),
            query: $payload
        );
    }

    public function cups(string $seasonId): Response
    {
        return $this->get('Cups', ['SeasonId' => $seasonId]);
    }

    public function cupsResults(string $cupId): Response
    {
        return $this->get('CupResults', ['CupId' => $cupId]);
    }

    public function athletes(string $familyName = '', string $givenName = ''): Response
    {
        return $this->get('Athletes', ['FamilyName' => $familyName, 'GivenName' => $givenName]);
    }

    public function athlete(string $ibuId): Response
    {
        return $this->get('CISBios', ['IBUId' => $ibuId]);
    }

    public function athleteResults(string $ibuId): Response
    {
        return $this->get('AllResults', ['IBUId' => $ibuId]);
    }

    public function events(string $seasonId, int $level = null): Response
    {
        return $this->get('Events', ['SeasonId' => $seasonId, 'Level' => $level]);
    }

    public function competitions(string $eventId): Response
    {
        return $this->get('Competitions', ['EventId' => $eventId]);
    }

    public function results(string $raceId): Response
    {
        return $this->get('Results', ['RaceId' => $raceId]);
    }

    public function stats(
        ?string $statisticId = null,
        ?string $statId = null,
        ?string $byWhat = null,
        ?string $genderId = null,
        ?string $seasonId = null,
        ?string $organizerId = null,
        ?string $ibuId = null,
        ?string $nat = null,
    ): Response
    {
        return $this->get('Stats', [
            "StatisticId" => $statisticId,
            "StatId" => $statId,
            "byWhat" => $byWhat,
            "SeasonId" => $seasonId,
            "OrganizerId" => $organizerId,
            "GenderId" => $genderId,
            "IBUId" => $ibuId,
            "Nat" => $nat,
        ]);
    }
}
