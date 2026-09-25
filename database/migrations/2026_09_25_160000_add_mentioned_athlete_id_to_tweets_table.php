<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tweets', function (Blueprint $table) {
            if (!Schema::hasColumn('tweets', 'mentioned_athlete_id')) {
                $table->foreignId('mentioned_athlete_id')->nullable()->after('media_urls')->constrained('athletes')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tweets', function (Blueprint $table) {
            if (Schema::hasColumn('tweets', 'mentioned_athlete_id')) {
                $table->dropConstrainedForeignId('mentioned_athlete_id');
            }
        });
    }
};
