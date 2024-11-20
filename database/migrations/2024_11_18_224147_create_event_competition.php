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
        Schema::create('event_competitions', function (Blueprint $table) {
            $table->id();
            $table->integer('event_id');
            $table->string('race_remote_id')->nullable();
            $table->string('km')->nullable();
            $table->string('cat_remote_id')->nullable();
            $table->string('discipline_remote_id')->nullable();
            $table->dateTime('start_time')->nullable();
            $table->string('description')->nullable();
            $table->string('short_description')->nullable();
            $table->string('location')->nullable();
            $table->string('nr_shootings')->nullable();
            $table->string('nr_spare_rounds')->nullable();
            $table->boolean('has_spare_rounds')->nullable();
            $table->integer('penalty_seconds')->nullable();
            $table->integer('nr_legs')->nullable();
            $table->string('shooting_positions')->nullable();
            $table->integer('local_utc_offset')->nullable();
            $table->string('rsc')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_competitions');
    }
};
