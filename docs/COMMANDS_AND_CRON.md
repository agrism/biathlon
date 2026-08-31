# Artisan Commands & Scheduled Tasks

This guide details all custom Artisan console commands, their responsibilities, operational flags, and scheduled cron execution in the Biathlon application.

---

## 1. Cron Schedule Configuration

Defined in `routes/console.php` and executed via Laravel's task scheduler:

```php
// Polls race results from IBU API every 5 minutes
Schedule::command('app:read-competition-results')->everyFiveMinutes();

// Finalizes completed forecasts and calculates user points every minute
Schedule::command('app:read-forecast-results-command')->everyMinute();

// Creates forecast entries for upcoming races daily
Schedule::command('app:generate-missing-forecasts')->daily();

// Updates athlete bios and statistics daily
Schedule::command('app:read-athletes')->daily();
```

To run the scheduler in local development:
```bash
php artisan schedule:work
```

In production, configure a standard crontab entry:
```cron
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 2. Command Reference Guide

### 1. `app:read-seasons`
- **File**: `app/Console/Commands/ReadSeasonsCommand.php`
- **Description**: Generates season identifiers in the database from 1957 up to the current year (e.g. `2425` for 2024/2025).
- **Run**:
  ```bash
  php artisan app:read-seasons
  ```

---

### 2. `app:read-events`
- **File**: `app/Console/Commands/ReadEventsCommand.php`
- **Description**: Iterates through all seasons and queries the IBU API for World Cup and IBU Cup events, storing stages, organizers, dates, and locations.
- **Run**:
  ```bash
  php artisan app:read-events
  ```

---

### 3. `app:read-competitions`
- **File**: `app/Console/Commands/ReadCompetitionsCommand.php`
- **Description**: Fetches all individual and relay races (competitions) for events starting from the current year, recording start times, disciplines, distances, and shooting configurations.
- **Run**:
  ```bash
  php artisan app:read-competitions
  ```

---

### 4. `app:read-athletes`
- **File**: `app/Console/Commands/ReadAthletesCommand.php`
- **Description**: Refreshes athlete biographies, national flags, photos, career trophies, and shooting/skiing statistics. Throttles requests by 3 seconds per athlete.
- **Run**:
  ```bash
  php artisan app:read-athletes
  ```

---

### 5. `app:read-competition-results`
- **File**: `app/Console/Commands/ReadCompetitionResultsCommand.php`
- **Description**: Checks upcoming and recent races (`start_time < now() + 2 days`) without recorded results (`results_handled_at IS NULL`). Fetches live start lists and final ranks from the IBU API, creating athlete records when missing. When official final results (`IsResult == true`) are returned, marks `results_handled_at = now()`.
- **Run**:
  ```bash
  php artisan app:read-competition-results
  ```

---

### 6. `app:generate-missing-forecasts`
- **File**: `app/Console/Commands/GenerateMissingForecastsCommand.php`
- **Description**: Scans competitions scheduled within the next 3 months and creates corresponding `Forecast` records with status `COMING`, setting the deadline (`submit_deadline_at`) equal to the competition start time.
- **Run**:
  ```bash
  php artisan app:generate-missing-forecasts
  ```

---

### 7. `app:read-forecast-results-command`
- **File**: `app/Console/Commands/ReadForecastResultsCommand.php`
- **Description**: Finds all forecasts in `COMING` status whose competition has finished (`results_handled_at IS NOT NULL`). Extracts the top 12 finishers, runs the active scoring engine (`ForecastTypeEnum::getHelper()`) for each participating user, stores regular and bonus points in `forecast_awards`, updates `final_data`, and transitions the forecast to `COMPLETED`.
- **Run**:
  ```bash
  php artisan app:read-forecast-results-command
  ```

---

### 8. `app:read-forecast-results-command-v2`
- **File**: `app/Console/Commands/ReadForecastResultsCommandV2.php`
- **Description**: Recalculates awards for completed forecasts using the classic `ForecastFirstSixPlacesServiceHelper`.
- **Run**:
  ```bash
  php artisan app:read-forecast-results-command-v2
  ```

---

## 3. Initial Project Data Seeding Runbook

To populate a fresh local environment with full IBU data:

```bash
# 1. Run migrations
php artisan migrate

# 2. Seed initial seasons
php artisan app:read-seasons

# 3. Pull events and stages
php artisan app:read-events

# 4. Pull competitions/races
php artisan app:read-competitions

# 5. Generate forecasts for upcoming races
php artisan app:generate-missing-forecasts

# 6. Fetch recent results and athlete stats
php artisan app:read-competition-results
php artisan app:read-athletes
```
