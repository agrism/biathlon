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
        Schema::create('athletes', function (Blueprint $table) {
            $table->id();
            $table->string('ibu_id');
            $table->boolean('is_team')->nullable();
            $table->string('family_name')->nullable();
            $table->string('given_name')->nullable();
            $table->string('other_family_names')->nullable();
            $table->string('other_given_names')->nullable();
            $table->string('nat')->nullable();
            $table->string('nf')->nullable();
            $table->dateTime('birth_date')->nullable();
            $table->string('gender_id')->nullable();
            $table->string('functions')->nullable();
            $table->string('badge_value')->nullable();
            $table->string('badge_order')->nullable();
            $table->string('photo_uri')->nullable();
            $table->string('flag_uri')->nullable();
            $table->timestamps();
        });

        Schema::create('event_competition_results', function (Blueprint $table) {
            $table->id();
            $table->integer('event_competition_id')->index();
            $table->integer('start_order')->nullable();
            $table->integer('result_order')->nullable();
            $table->string('irm')->nullable();
            $table->integer('athlete_id')->index();
            $table->string('bib')->nullable();
            $table->integer('leg')->nullable();
            $table->string('rank')->nullable();
            $table->string('shootings')->nullable();
            $table->string('shooting_total')->nullable();
            $table->string('run_time')->nullable();
            $table->string('total_time')->nullable();
            $table->string('wc')->nullable();
            $table->string('nc')->nullable();
            $table->string('noc')->nullable();
            $table->string('start_time')->nullable();
            $table->string('start_info')->nullable();
            $table->string('start_row')->nullable();
            $table->string('start_lane')->nullable();
            $table->string('bib_color')->nullable();
            $table->string('behind')->nullable();
            $table->string('start_group')->nullable();
            $table->string('team_id')->nullable();
            $table->string('pursuit_start_distance')->nullable();
            $table->string('result')->nullable();
            $table->string('leg_rank')->nullable();
            $table->string('team_rank_after_leg')->nullable();
            $table->string('start_confirmed')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_competition_results');
        Schema::dropIfExists('athletes');
    }
};
