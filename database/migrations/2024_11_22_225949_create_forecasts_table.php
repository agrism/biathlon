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
        Schema::create('forecasts', function (Blueprint $table) {
            $table->id();
            $table->integer('event_competition_id')->index();
            $table->string('name');
            $table->string('type');
            $table->string('status');
            $table->string('submit_deadline_at');
            $table->json('final_data')->nullable();
            $table->timestamps();
        });

        Schema::create('forecast_submitted_data', function (Blueprint $table) {
            $table->id();
            $table->integer('forecast_id')->index();
            $table->integer('user_id')->index();
            $table->json('submitted_data');
            $table->timestamps();
        });

        Schema::create('forecast_awards', function (Blueprint $table) {
            $table->id();
            $table->integer('forecast_id')->index();
            $table->integer('user_id')->index();
            $table->float('points');
            $table->timestamps();
        });

        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->index();
            $table->string('type')->index();
            $table->integer('type_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('forecast_awards');
        Schema::dropIfExists('forecast_submitted_data');
        Schema::dropIfExists('forecasts');
    }
};
