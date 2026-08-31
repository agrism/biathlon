# IBU Sport API Integration Guide

This document documents the external API client used to synchronize official biathlon data from the International Biathlon Union (IBU) into the local application.

---

## 1. Overview & Service Client

The application interacts with the IBU Sport API via `App\Services\BiathlonResultApi` (`app/Services/BiathlonResultApi.php`).

- **Base URL**: `https://biathlonresults.com/modules/sportapi/api/`
- **Protocol**: HTTPS GET requests
- **Response Format**: JSON
- **HTTP Client**: Built upon Laravel's `Illuminate\Support\Facades\Http`

```php
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
    // ...
}
```

---

## 2. API Endpoints Reference

### 1. Events & Stages
- **Method**: `events(string $seasonId, int $level = null): Response`
- **Path**: `GET /Events`
- **Parameters**:
  - `SeasonId` (string, required): e.g. `'2425'`
  - `Level` (int, optional): Level 1 indicates BMW IBU World Cup, Level 2 indicates IBU Cup, Level 3 indicates Junior Cup.
- **Consumer**: `App\Console\Commands\ReadEventsCommand`
- **Key Response Fields**:
  - `EventId`: Remote event identifier (e.g. `'BT2425SWRLCP01'`)
  - `StartDate`, `EndDate`, `FirstCompetitionDate`
  - `Organizer`, `Nat`, `NatLong`
  - `Description`, `ShortDescription`, `Altitude`

---

### 2. Competitions (Races)
- **Method**: `competitions(string $eventId): Response`
- **Path**: `GET /Competitions`
- **Parameters**:
  - `EventId` (string, required): Remote event ID (e.g. `'BT2425SWRLCP01'`)
- **Consumer**: `App\Console\Commands\ReadCompetitionsCommand`
- **Key Response Fields**:
  - `RaceId`: Remote race identifier (e.g. `'BT2425SWRLCP01SMIN'`)
  - `DisciplineId`: Discipline code (`'SP'`, `'PU'`, `'IN'`, `'MS'`, `'RL'`, `'SR'`, etc.)
  - `catId`: Category code (`'SM'`, `'SW'`, `'JM'`, `'JW'`, `'MX'`, etc.)
  - `StartTime`: ISO 8601 UTC race start timestamp
  - `NrShootings`, `NrSpareRounds`, `HasSpareRounds`, `PenaltySeconds`

---

### 3. Competition Results & Start Lists
- **Method**: `results(string $raceId): Response`
- **Path**: `GET /Results`
- **Parameters**:
  - `RaceId` (string, required): Remote race ID
- **Consumer**: `App\Console\Commands\ReadCompetitionResultsCommand`
- **Key Response Fields**:
  - `IsStartList` (bool): True if race has not started and start list is available.
  - `IsResult` (bool): True if final results are confirmed.
  - `Competition.StatusId` / `Competition.StatusText`: Official race status.
  - `Results`: Array of athlete results:
    - `IBUId`: Athlete's unique IBU identifier.
    - `Rank`: Final finish rank (null or empty string if not finished / disqualified / in progress).
    - `Bib`: Bib number.
    - `Shootings`: Shooting string (e.g. `'0+1 1+3'`).
    - `ShootingTotal`: Total penalties (e.g. `'1'`).
    - `TotalTime`: Formatted final duration (e.g. `'32:15.4'`).
    - `Behind`: Time behind leader (e.g. `'+12.4'`).
    - `WC`: World Cup points earned for the race.

---

### 4. Athlete Bios & Historical Statistics
- **Method**: `athlete(string $ibuId): Response`
- **Path**: `GET /CISBios`
- **Parameters**:
  - `IBUId` (string, required): Remote athlete ID (e.g. `'BTFRA11608199201'`)
- **Consumer**: `App\Console\Commands\ReadAthletesCommand`
- **Key Response Fields**:
  - `FamilyName`, `GivenName`, `otherFamilyNames`, `otherGivenNames`
  - `NAT`, `NF` (National Federation), `Birthdate`, `GenderId`
  - `PhotoURI`, `FlagURI`
  - `StatSeasons`: List of seasons with stats.
  - `RNKS`: World Cup discipline rank and point history.
  - `StatSkiing`, `StatSkiKMB`: Skiing speed percentages.
  - `StatShooting`, `StatShootingProne`, `StatShootingStanding`: Shooting accuracy percentages.
  - `Badges`: Career awards and medals.

---

### 5. Other Available Endpoints
- `cups(string $seasonId)`: Retrieve list of cups (e.g. World Cup Overall, Sprint Cup, Relay Cup).
- `cupsResults(string $cupId)`: Retrieve cup standings.
- `athletes(string $familyName = '', string $givenName = '')`: Search athlete database by name.
- `athleteResults(string $ibuId)`: Retrieve full historical race results for an athlete.
- `stats(...)`: Query generalized IBU statistical summaries.

---

## 3. Rate Limiting & Performance Considerations

1. **Polite Crawling & Delays**:
   - `ReadAthletesCommand` includes a 3-second sleep between athlete biography requests to avoid overwhelming the IBU API.
   - `ReadCompetitionResultsCommand` utilizes micro-delays (`usleep(500)`) between races.
2. **Selective Updating**:
   - Athlete bios are only updated if `details_updated_at` is older than the current day's start (`details_updated_at->gt(now()->startOfDay())`).
   - Forecast results are calculated only once when `EventCompetition::results_handled_at` is set.
