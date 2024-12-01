<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('event_competitions', function (Blueprint $table) {
            $table->timestamp('results_handled_at')->nullable()->after('rsc');
        });

        Schema::table('forecast_awards', function (Blueprint $table) {
            $table->string('type')->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forecast_awards', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        Schema::table('event_competitions', function (Blueprint $table) {
            $table->dropColumn('results_handled_at');
        });
    }
};
