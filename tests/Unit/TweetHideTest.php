<?php

namespace Tests\Unit;

use App\Models\Tweet;
use App\Models\User;
use App\Services\BiathlonTweetService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TweetHideTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);

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
            $table->boolean('should_hide')->default(false);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('User');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->default('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function test_tweet_table_has_should_hide_column(): void
    {
        $this->assertTrue(Schema::hasColumn('tweets', 'should_hide'));
    }

    public function test_tweet_model_casts_should_hide_to_boolean(): void
    {
        $tweet = Tweet::create([
            'tweet_id' => 'test_1',
            'author_name' => 'Penalty Loop',
            'author_handle' => 'penaltyloop',
            'content' => 'Test content',
            'published_at' => now(),
            'should_hide' => 1,
        ]);

        $this->assertIsBool($tweet->should_hide);
        $this->assertTrue($tweet->should_hide);

        $tweetDefault = Tweet::create([
            'tweet_id' => 'test_2',
            'author_name' => 'Penalty Loop',
            'author_handle' => 'penaltyloop',
            'content' => 'Test content 2',
            'published_at' => now(),
        ]);

        $this->assertFalse($tweetDefault->should_hide);
    }

    public function test_non_admin_users_do_not_see_hidden_tweets(): void
    {
        $visibleTweet = Tweet::create([
            'tweet_id' => 'visible_1',
            'content' => 'Visible tweet',
            'published_at' => now(),
            'should_hide' => false,
        ]);

        $hiddenTweet = Tweet::create([
            'tweet_id' => 'hidden_1',
            'content' => 'Hidden tweet',
            'published_at' => now(),
            'should_hide' => true,
        ]);

        $service = new BiathlonTweetService();

        // 1. Guest
        $paged = $service->getPagedTweets();
        $this->assertCount(1, $paged->items());
        $this->assertEquals($visibleTweet->id, $paged->items()[0]->id);

        // 2. Regular customer / other email
        $regularUser = new User();
        $regularUser->email = 'other@example.com';
        $this->actingAs($regularUser);

        $pagedUser = $service->getPagedTweets();
        $this->assertCount(1, $pagedUser->items());
        $this->assertEquals($visibleTweet->id, $pagedUser->items()[0]->id);
    }

    public function test_admin_with_inbox_email_sees_hidden_and_visible_tweets(): void
    {
        $visibleTweet = Tweet::create([
            'tweet_id' => 'visible_1',
            'content' => 'Visible tweet',
            'published_at' => now()->subMinute(),
            'should_hide' => false,
        ]);

        $hiddenTweet = Tweet::create([
            'tweet_id' => 'hidden_1',
            'content' => 'Hidden tweet',
            'published_at' => now(),
            'should_hide' => true,
        ]);

        $adminUser = new User();
        $adminUser->email = '7924@inbox.lv';
        $this->actingAs($adminUser);

        $service = new BiathlonTweetService();
        $paged = $service->getPagedTweets();

        $this->assertCount(2, $paged->items());
    }

    public function test_unauthorized_users_cannot_toggle_tweet_hide(): void
    {
        $tweet = Tweet::create([
            'tweet_id' => 'test_toggle_1',
            'content' => 'Tweet to toggle',
            'published_at' => now(),
            'should_hide' => false,
        ]);

        // Guest
        $response = $this->post(route('tweets.toggle-hide', $tweet->id));
        $response->assertStatus(403);

        // Other user
        $user = new User();
        $user->email = 'user@example.com';
        $this->actingAs($user);

        $response2 = $this->post(route('tweets.toggle-hide', $tweet->id));
        $response2->assertStatus(403);
    }

    public function test_admin_can_toggle_tweet_hide(): void
    {
        $tweet = Tweet::create([
            'tweet_id' => 'test_toggle_2',
            'content' => 'Tweet to toggle by admin',
            'published_at' => now(),
            'should_hide' => false,
        ]);

        $admin = new User();
        $admin->email = '7924@inbox.lv';
        $this->actingAs($admin);

        // Toggle to hidden
        $response = $this->post(route('tweets.toggle-hide', $tweet->id));
        $response->assertStatus(200);
        $response->assertSee('Hidden from clients');
        $response->assertSee('hide-loader-' . $tweet->id);

        $tweet->refresh();
        $this->assertTrue($tweet->should_hide);

        // Toggle back to visible
        $response2 = $this->post(route('tweets.toggle-hide', $tweet->id));
        $response2->assertStatus(200);
        $response2->assertSee('Visible to clients');

        $tweet->refresh();
        $this->assertFalse($tweet->should_hide);
    }
}
