<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tweets', function (Blueprint $table) {
            $table->id();
            $table->string('tweet_id')->unique();
            $table->string('author_name')->default('Penalty Loop');
            $table->string('author_handle')->default('penaltyloop');
            $table->string('author_avatar')->nullable();
            $table->text('content');
            $table->json('media_urls')->nullable();
            $table->integer('likes_count')->default(0);
            $table->integer('retweets_count')->default(0);
            $table->string('tweet_url')->nullable();
            $table->dateTime('published_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tweets');
    }
};
