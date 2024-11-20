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
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->string('name_remote');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->integer('season_id');
            $table->string('trimester')->nullable();
            $table->string('event_remote_id')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->dateTime('first_competition_date')->nullable();
            $table->string('description')->nullable();
            $table->string('event_series_no')->nullable();
            $table->string('short_description')->nullable();
            $table->integer('altitude')->nullable();
            $table->string('organizer_remote_id')->nullable();
            $table->string('organizer')->nullable();
            $table->string('nat')->nullable();
            $table->string('nat_long')->nullable();
            $table->string('medal_set_id')->nullable();
            $table->string('event_classification_id')->nullable();
            $table->integer('level')->nullable();
            $table->integer('utc_offset')->nullable();
            $table->integer('event_status_id')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
        Schema::dropIfExists('seasons');
    }
};
