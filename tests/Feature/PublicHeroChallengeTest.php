<?php

namespace Tests\Feature;

use App\Models\HeroChallenge;
use Database\Seeders\HeroChallengeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicHeroChallengeTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_the_active_database_challenge_without_exposing_the_answer_key(): void
    {
        $this->seed(HeroChallengeSeeder::class);

        $challenge = HeroChallenge::query()->with('options')->firstOrFail();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($challenge->title)
            ->assertSee($challenge->prompt)
            ->assertSee(route('hero-challenges.answer', $challenge), false)
            ->assertDontSee('is_correct')
            ->assertDontSee('hero_challenge_options');
    }

    public function test_advanced_seeded_challenges_are_ordered_and_have_one_correct_answer_each(): void
    {
        $this->seed(HeroChallengeSeeder::class);

        $challenges = HeroChallenge::query()
            ->where('is_active', true)
            ->with('options')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(3)
            ->get();

        $this->assertCount(3, $challenges);
        $this->assertSame('hero-difference-squares', $challenges[0]->slug);
        $this->assertSame('hero-recursive-three', $challenges[1]->slug);
        $this->assertSame('hero-factorial-offset', $challenges[2]->slug);

        foreach ($challenges as $challenge) {
            $this->assertCount(4, $challenge->options);
            $this->assertSame(1, $challenge->options->where('is_correct', true)->count());
        }
    }

    public function test_correct_answer_is_checked_on_the_server_and_returns_the_explanation(): void
    {
        $this->seed(HeroChallengeSeeder::class);

        $challenge = HeroChallenge::query()->with('options')->firstOrFail();
        $correctOption = $challenge->options->firstWhere('is_correct', true);

        $this->post(route('hero-challenges.answer', $challenge), [
            'option_id' => $correctOption->id,
        ])
            ->assertRedirect(route('home', ['challenge_id' => $challenge->id]) . '#hero-challenge')
            ->assertSessionHas('hero_challenge_result.correct', true)
            ->assertSessionHas('hero_challenge_result.explanation', $challenge->explanation);
    }

    public function test_wrong_answer_does_not_reveal_the_correct_answer_or_explanation(): void
    {
        $this->seed(HeroChallengeSeeder::class);

        $challenge = HeroChallenge::query()->with('options')->firstOrFail();
        $wrongOption = $challenge->options->firstWhere('is_correct', false);

        $this->post(route('hero-challenges.answer', $challenge), [
            'option_id' => $wrongOption->id,
        ])
            ->assertRedirect(route('home', ['challenge_id' => $challenge->id]) . '#hero-challenge')
            ->assertSessionHas('hero_challenge_result.correct', false)
            ->assertSessionMissing('hero_challenge_result.explanation');
    }

    public function test_an_option_from_another_challenge_cannot_be_submitted(): void
    {
        $this->seed(HeroChallengeSeeder::class);

        $challenge = HeroChallenge::query()->with('options')->firstOrFail();
        $otherChallenge = HeroChallenge::query()->create([
            'slug' => 'inactive-foreign-challenge',
            'title' => 'معمای دیگر',
            'prompt' => 'سؤال دیگر',
            'explanation' => 'توضیح دیگر',
            'is_active' => false,
            'sort_order' => 99,
        ]);
        $foreignOption = $otherChallenge->options()->create([
            'label' => 'A',
            'content' => 'گزینه',
            'is_correct' => true,
            'sort_order' => 1,
        ]);

        $this->from(route('home'))
            ->post(route('hero-challenges.answer', $challenge), [
                'option_id' => $foreignOption->id,
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors('option_id');
    }

    public function test_inactive_challenge_cannot_be_answered(): void
    {
        $this->seed(HeroChallengeSeeder::class);

        $challenge = HeroChallenge::query()->firstOrFail();
        $challenge->update(['is_active' => false]);

        $this->post(route('hero-challenges.answer', $challenge), [
            'option_id' => $challenge->options()->value('id'),
        ])->assertNotFound();
    }
}
