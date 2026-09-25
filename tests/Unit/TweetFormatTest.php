<?php

namespace Tests\Unit;

use App\Models\Tweet;
use Tests\TestCase;

class TweetFormatTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
    }

    public function test_decodes_html_entities(): void
    {
        $tweet = new Tweet([
            'content' => "It was late in the 4th lap of the Men&#8217;s Pursuit in Holmenkollen. &#8220;Amazing&#8221; race &#8211; &amp; excitement!",
        ]);

        $formatted = $tweet->getFormattedContent();

        $this->assertStringNotContainsString('&#8217;', $formatted);
        $this->assertStringNotContainsString('&#8220;', $formatted);
        $this->assertStringNotContainsString('&amp;#8217;', $formatted);
        $this->assertStringContainsString('Men’s Pursuit', $formatted);
        $this->assertStringContainsString('“Amazing” race – &amp; excitement!', $formatted);
    }

    public function test_formats_article_header_with_badge(): void
    {
        $tweet = new Tweet([
            'content' => "📝 Sterling Sturla\nIt was late in the 4th lap of the Men’s Pursuit.",
        ]);

        $formatted = $tweet->getFormattedContent();

        $this->assertStringContainsString('Sterling Sturla', $formatted);
        $this->assertStringContainsString('📝', $formatted);
        $this->assertStringContainsString('font-bold', $formatted);
        $this->assertStringContainsString('It was late in the 4th lap', $formatted);
    }

    public function test_formats_numbered_rankings_with_badges(): void
    {
        $tweet = new Tweet([
            'content' => "French Summer Cup Pursuit\n1) Eric Perrot (19/20)\n2) Fabien Claude (18) +1:54\n3) Florent Claude (20) +3:02\n4) Quentin Fillon Maillet (15) +3:47",
        ]);

        $formatted = $tweet->getFormattedContent();

        $this->assertStringContainsString('Eric Perrot', $formatted);
        $this->assertStringContainsString('bg-amber-100', $formatted); // Gold for 1st
        $this->assertStringContainsString('bg-slate-200', $formatted); // Silver for 2nd
        $this->assertStringContainsString('bg-amber-700/15', $formatted); // Bronze for 3rd
        $this->assertStringContainsString('bg-slate-100', $formatted); // 4th
    }

    public function test_formats_bullet_items(): void
    {
        $tweet = new Tweet([
            'content' => "New Podcast!\n- Highlights of the season\n- Canadian biathlon update\n- Winter preparation",
        ]);

        $formatted = $tweet->getFormattedContent();

        $this->assertStringContainsString('Highlights of the season', $formatted);
        $this->assertStringContainsString('•', $formatted);
    }

    public function test_formats_urls_mentions_and_hashtags(): void
    {
        $tweet = new Tweet([
            'content' => "Check out @penaltyloop at https://penaltyloop.com/article #biathlon",
        ]);

        $formatted = $tweet->getFormattedContent();

        $this->assertStringContainsString('<span class="text-sky-600 font-bold">@penaltyloop</span>', $formatted);
        $this->assertStringContainsString('<span class="text-sky-600 font-semibold">#biathlon</span>', $formatted);
        $this->assertStringContainsString('<a href="https://penaltyloop.com/article"', $formatted);
    }

    public function test_finds_first_mentioned_athlete(): void
    {
        \Illuminate\Support\Facades\Schema::create('athletes', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('given_name')->nullable();
            $table->string('family_name')->nullable();
            $table->string('nat')->nullable();
            $table->string('photo_uri')->nullable();
            $table->string('ibu_id')->nullable();
            $table->timestamps();
        });

        $athleteJtb = new \App\Models\Athlete();
        $athleteJtb->given_name = 'Johannes Thingnes';
        $athleteJtb->family_name = 'BOE';
        $athleteJtb->nat = 'NOR';
        $athleteJtb->photo_uri = 'https://ibu.blob.core.windows.net/docs/athletes/BTNOR11605199301.png';
        $athleteJtb->save();

        $athletePerrot = new \App\Models\Athlete();
        $athletePerrot->given_name = 'Eric';
        $athletePerrot->family_name = 'PERROT';
        $athletePerrot->nat = 'FRA';
        $athletePerrot->photo_uri = 'https://ibu.blob.core.windows.net/docs/athletes/BTFRA10702197201.png';
        $athletePerrot->save();

        \Illuminate\Support\Facades\Cache::flush();

        $tweet1 = new Tweet([
            'content' => "⚡They can still mathematically win the overall ranking:\n🇳🇴 J.T.Boe\n🇳🇴 S.H.Laegreid (-5)",
        ]);
        $found1 = $tweet1->findFirstMentionedAthlete();
        $this->assertNotNull($found1);
        $this->assertEquals($athleteJtb->id, $found1->id);

        $tweet2 = new Tweet([
            'content' => "⚡Eric Perrot : I think I am better than last year...",
        ]);
        $found2 = $tweet2->findFirstMentionedAthlete();
        $this->assertNotNull($found2);
        $this->assertEquals($athletePerrot->id, $found2->id);

        $tweetNone = new Tweet([
            'content' => "⚡Happy 2 digits day ! 99 to go...",
        ]);
        $this->assertNull($tweetNone->findFirstMentionedAthlete());
    }
}
