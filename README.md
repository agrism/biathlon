# Biathlon 🎯🎿

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-3.4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![HTMX](https://img.shields.io/badge/HTMX-1.9-336699?style=for-the-badge&logo=htmx&logoColor=white)](https://htmx.org)

**Biathlon** is a modern Laravel web application and fantasy prediction platform for the **IBU World Cup Biathlon**. It synchronizes real-time race results, start lists, and athlete performance statistics directly from the International Biathlon Union (IBU) Sport API, powering an interactive 6-place prediction league with automated scoring engines.

---

## ✨ Features

- 🏆 **Real-Time IBU Sync**: Automatic synchronization of World Cup seasons, stages, race formats (Sprint, Pursuit, Individual, Mass Start, Relays), live results, and athlete bios.
- 🎯 **Forecast (Prediction) Game**: Select your predicted top 6 athletes before the race start deadline.
- 🧮 **Pluggable Scoring Engines**:
  - **Dainis Scheme**: Precision-delta matrix scoring ($|\text{predicted} - \text{actual}|$) with podium medal bonuses.
  - **Classic Scheme**: Exact position rewards (25/20/15/5/5/5) with perfection and combination bonuses.
- ⚡ **Interactive HTMX UI**: Fast, server-driven reactive UI powered by HTMX, Alpine.js, and Tailwind CSS.
- 📊 **Multi-Season Leaderboards**: Comprehensive season breakdown tracking user totals, stage winners, and point differentials.
- 🔒 **Secure Auth & 2FA**: Laravel Fortify authentication with profile customization and favorite athletes bookmarking.

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.2 or higher (with `pdo`, `mbstring`, `curl`, `json` extensions)
- Composer
- Node.js (v18+) & npm
- MySQL (Production/Staging) or SQLite (Local)

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/agrism/biathlon.git
cd biathlon

# 2. Install PHP and Node dependencies
composer install
npm install

# 3. Environment configuration
cp .env.example .env
php artisan key:generate

# 4. Run database migrations
php artisan migrate

# 5. Build frontend assets
npm run build
```

### Local Development Server

Run the development server, queue worker, logs, and Vite asset compiler concurrently:

```bash
composer run dev
```

Or run services individually:
```bash
php artisan serve
npm run dev
```

---

## ⚙️ Initial Data Sync

Populate your local database with official IBU data using the custom Artisan synchronization commands:

```bash
# Sync seasons, events, and races
php artisan app:read-seasons
php artisan app:read-events
php artisan app:read-competitions

# Generate forecasts for upcoming races
php artisan app:generate-missing-forecasts

# Fetch current athlete bios, shooting/skiing stats, and race results
php artisan app:read-competition-results
php artisan app:read-athletes
```

---

## 🧪 Testing

Run the automated PHPUnit test suite:

```bash
./vendor/bin/phpunit tests/Unit
```

---

## 📚 Documentation Index

For in-depth guides and technical documentation:

- 🤖 **[AGENTS.md](AGENTS.md)** — Comprehensive onboarding guide for AI assistants and engineers.
- 🏛️ **[docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)** — Architectural design, Domain models, Value Objects, and HTMX UI patterns.
- 🧮 **[docs/SCORING_SYSTEM.md](docs/SCORING_SYSTEM.md)** — Mathematical specification of the prediction scoring engines.
- 🌐 **[docs/API_INTEGRATION.md](docs/API_INTEGRATION.md)** — IBU Sport API endpoints and data mapping reference.
- ⏱️ **[docs/COMMANDS_AND_CRON.md](docs/COMMANDS_AND_CRON.md)** — Artisan console commands and cron task scheduling.

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).
