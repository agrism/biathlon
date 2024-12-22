<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('athletes', function (Blueprint $table) {
            $table->json('details')->after('flag_uri')->nullable();
            $table->timestamp('details_updated_at')->after('details')->nullable();
        });

        DB::statement(
            "
                ALTER TABLE athletes
                ADD COLUMN stat_season VARCHAR(10) GENERATED ALWAYS AS (
                    CASE
                        WHEN details IS NULL THEN NULL
                        WHEN JSON_VALUE(details, '$.StatSeasons[0]') = '' THEN NULL
                        WHEN JSON_VALUE(details, '$.StatSeasons[0]') IS NULL THEN NULL
                        ELSE JSON_VALUE(details, '$.StatSeasons[0]')
                    END
                ) STORED AFTER details_updated_at
            "
        );

        DB::statement(
            "
                ALTER TABLE athletes
                ADD COLUMN stat_p_total DECIMAL(5,2) GENERATED ALWAYS AS (
                    CASE
                        WHEN details IS NULL THEN NULL
                        WHEN JSON_VALUE(details, '$.RNKS[0]') = '' THEN NULL
                        WHEN JSON_VALUE(details, '$.RNKS[0]') IS NULL THEN NULL
                        WHEN JSON_VALUE(details, '$.RNKS[0].Total') = '' THEN NULL
                        WHEN JSON_VALUE(details, '$.RNKS[0].Total') IS NULL THEN NULL
                        ELSE CAST(JSON_VALUE(details, '$.RNKS[0].Total') AS DECIMAL(5,2))
                    END
                ) STORED AFTER stat_season
            "
        );

        DB::statement(
            "
                ALTER TABLE athletes
                ADD COLUMN stat_p_sprint DECIMAL(5,2) GENERATED ALWAYS AS (
                    CASE
                        WHEN details IS NULL THEN NULL
                        WHEN JSON_VALUE(details, '$.RNKS[0]') = '' THEN NULL
                        WHEN JSON_VALUE(details, '$.RNKS[0]') IS NULL THEN NULL
                        WHEN JSON_VALUE(details, '$.RNKS[0].Sprint') = '' THEN NULL
                        WHEN JSON_VALUE(details, '$.RNKS[0].Sprint') IS NULL THEN NULL
                        ELSE CAST(JSON_VALUE(details, '$.RNKS[0].Sprint') AS DECIMAL(5,2))
                    END
                ) STORED AFTER stat_p_total
            "
        );

        DB::statement(
            "
                ALTER TABLE athletes
                ADD COLUMN stat_p_individual DECIMAL(5,2) GENERATED ALWAYS AS (
                    CASE
                        WHEN details IS NULL THEN NULL
                        WHEN JSON_VALUE(details, '$.RNKS[0]') = '' THEN NULL
                        WHEN JSON_VALUE(details, '$.RNKS[0]') IS NULL THEN NULL
                        WHEN JSON_VALUE(details, '$.RNKS[0].Individual') = '' THEN NULL
                        WHEN JSON_VALUE(details, '$.RNKS[0].Individual') IS NULL THEN NULL
                        ELSE CAST(JSON_VALUE(details, '$.RNKS[0].Individual') AS DECIMAL(5,2))
                    END
                ) STORED AFTER stat_p_sprint
            "
        );

        DB::statement(
            "
                ALTER TABLE athletes
                ADD COLUMN stat_p_pursuit DECIMAL(5,2) GENERATED ALWAYS AS (
                    CASE
                        WHEN details IS NULL THEN NULL
                        WHEN JSON_VALUE(details, '$.RNKS[0]') = '' THEN NULL
                        WHEN JSON_VALUE(details, '$.RNKS[0]') IS NULL THEN NULL
                        WHEN JSON_VALUE(details, '$.RNKS[0].Pursuit') = '' THEN NULL
                        WHEN JSON_VALUE(details, '$.RNKS[0].Pursuit') IS NULL THEN NULL
                        ELSE CAST(JSON_VALUE(details, '$.RNKS[0].Pursuit') AS DECIMAL(5,2))
                    END
                ) STORED AFTER stat_p_individual
            "
        );

        DB::statement(
            "
                ALTER TABLE athletes
                ADD COLUMN stat_p_mass DECIMAL(5,2) GENERATED ALWAYS AS (
                    CASE
                        WHEN details IS NULL THEN NULL
                        WHEN JSON_VALUE(details, '$.RNKS[0]') = '' THEN NULL
                        WHEN JSON_VALUE(details, '$.RNKS[0]') IS NULL THEN NULL
                        WHEN JSON_VALUE(details, '$.RNKS[0].MassStart') = '' THEN NULL
                        WHEN JSON_VALUE(details, '$.RNKS[0].MassStart') IS NULL THEN NULL
                        ELSE CAST(JSON_VALUE(details, '$.RNKS[0].MassStart') AS DECIMAL(5,2))
                    END
                ) STORED AFTER stat_p_pursuit
            "
        );

        DB::statement(
            "
                ALTER TABLE athletes
                ADD COLUMN stat_ski_kmb DECIMAL(5,2) GENERATED ALWAYS AS (
                    CASE
                        WHEN details IS NULL THEN NULL
                        WHEN JSON_VALUE(details, '$.StatSkiKMB[0]') = '' THEN NULL
                        WHEN JSON_VALUE(details, '$.StatSkiKMB[0]') IS NULL THEN NULL
                        ELSE CAST(REPLACE(JSON_VALUE(details, '$.StatSkiKMB[0]'), '%', '') AS DECIMAL(5,2))
                    END
                ) STORED AFTER stat_p_mass
            "
        );

        DB::statement(
            "
                ALTER TABLE athletes
                ADD COLUMN stat_skiing DECIMAL(5,2) GENERATED ALWAYS AS (
                    CASE
                        WHEN details IS NULL THEN NULL
                        WHEN JSON_VALUE(details, '$.StatSkiing[0]') = '' THEN NULL
                        WHEN JSON_VALUE(details, '$.StatSkiing[0]') IS NULL THEN NULL
                        ELSE CAST(REPLACE(JSON_VALUE(details, '$.StatSkiing[0]'), '%', '') AS DECIMAL(5,2))
                    END
                ) STORED AFTER stat_ski_kmb
            "
        );

        DB::statement(
            "
                ALTER TABLE athletes
                ADD COLUMN stat_shooting DECIMAL(5,2) GENERATED ALWAYS AS (
                    CASE
                        WHEN details IS NULL THEN NULL
                        WHEN JSON_VALUE(details, '$.StatShooting[0]') = '' THEN NULL
                        WHEN JSON_VALUE(details, '$.StatShooting[0]') IS NULL THEN NULL
                        ELSE CAST(REPLACE(JSON_VALUE(details, '$.StatShooting[0]'), '%', '') AS DECIMAL(5,2))
                    END
                ) STORED AFTER stat_skiing
            "
        );

        DB::statement(
            "
                ALTER TABLE athletes
                ADD COLUMN stat_shooting_prone DECIMAL(5,2) GENERATED ALWAYS AS (
                    CASE
                        WHEN details IS NULL THEN NULL
                        WHEN JSON_VALUE(details, '$.StatShootingProne[0]') = '' THEN NULL
                        WHEN JSON_VALUE(details, '$.StatShootingProne[0]') IS NULL THEN NULL
                        ELSE CAST(REPLACE(JSON_VALUE(details, '$.StatShootingProne[0]'), '%', '') AS DECIMAL(5,2))
                    END
                ) STORED AFTER stat_shooting
            "
        );

        DB::statement(
            "
                ALTER TABLE athletes
                ADD COLUMN stat_shooting_standing DECIMAL(5,2) GENERATED ALWAYS AS (
                    CASE
                        WHEN details IS NULL THEN NULL
                        WHEN JSON_VALUE(details, '$.StatShootingStanding[0]') = '' THEN NULL
                        WHEN JSON_VALUE(details, '$.StatShootingStanding[0]') IS NULL THEN NULL
                        ELSE CAST(REPLACE(JSON_VALUE(details, '$.StatShootingStanding[0]'), '%', '') AS DECIMAL(5,2))
                    END
                ) STORED AFTER stat_shooting_prone
            "
        );

        Schema::table('event_competition_results', function (Blueprint $table) {
            $table->json('stat_details')->after('start_confirmed')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_competition_results', function (Blueprint $table) {
            $table->dropColumn('stat_details');
        });

        Schema::table('athletes', function (Blueprint $table) {
            $table->dropColumn('stat_shooting_standing');
            $table->dropColumn('stat_shooting_prone');
            $table->dropColumn('stat_shooting');
            $table->dropColumn('stat_skiing');
            $table->dropColumn('stat_ski_kmb');
            $table->dropColumn('stat_p_mass');
            $table->dropColumn('stat_p_pursuit');
            $table->dropColumn('stat_p_individual');
            $table->dropColumn('stat_p_sprint');
            $table->dropColumn('stat_p_total');
            $table->dropColumn('stat_season');

            $table->dropColumn('details');
            $table->dropColumn('details_updated_at');
        });
    }
};
