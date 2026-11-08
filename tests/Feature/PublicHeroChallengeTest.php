<?php

namespace Tests\Feature;

use App\Models\HeroChallenge;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\HeroChallengeSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class PublicHeroChallengeTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_the_active_database_challenge_without_exposing_the_answer_key(): void
    {
        $this->seed(HeroChallengeSeeder::class);

        $challenge = HeroChallenge::query()->with('options')->orderBy('sort_order')->orderBy('id')->firstOrFail();

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
        $this->assertSame('hero-three-mislabeled-boxes', $challenges[0]->slug);
        $this->assertSame('hero-two-uneven-ropes', $challenges[1]->slug);
        $this->assertSame('hero-three-switches', $challenges[2]->slug);

        foreach ($challenges as $challenge) {
            $this->assertCount(4, $challenge->options);
            $this->assertSame(1, $challenge->options->where('is_correct', true)->count());
        }
    }

    public function test_incomplete_active_challenge_is_skipped_without_hiding_valid_challenges(): void
    {
        $this->seed(HeroChallengeSeeder::class);

        $incomplete = HeroChallenge::query()
            ->with('options')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->firstOrFail();
        $nextValid = HeroChallenge::query()->where('slug', 'hero-two-uneven-ropes')->firstOrFail();

        $incomplete->options()->orderBy('id')->firstOrFail()->delete();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($nextValid->title)
            ->assertDontSee($incomplete->title);
    }

    public function test_owner_editor_restores_the_exact_four_option_contract_when_saving_a_challenge(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(HeroChallengeSeeder::class);

        $editor = User::factory()->create(['status' => 'active']);
        $seoRole = Role::query()->where('slug', 'seo-specialist')->firstOrFail();
        $editor->roles()->attach($seoRole->id);

        $challenge = HeroChallenge::query()
            ->with('options')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->firstOrFail();
        $challenge->options()->create([
            'label' => 'E',
            'content' => 'گزینه اضافه',
            'is_correct' => false,
            'sort_order' => 5,
        ]);

        $this->actingAs($editor)
            ->patch(route('owner.website.hero-challenge.update', $challenge), [
                'title' => $challenge->title,
                'prompt' => $challenge->prompt,
                'sequence_text' => '',
                'explanation' => $challenge->explanation,
                'options' => [
                    'A' => 'پاسخ اول',
                    'B' => 'پاسخ دوم',
                    'C' => 'پاسخ سوم',
                    'D' => 'پاسخ چهارم',
                ],
                'correct_option' => 'C',
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->assertRedirect(route('owner.website.hero-challenges.index'))
            ->assertSessionHas('success');

        $this->assertSame(
            ['A', 'B', 'C', 'D'],
            $challenge->options()->orderBy('sort_order')->pluck('label')->all(),
        );
        $this->assertSame(1, $challenge->options()->where('is_correct', true)->count());
    }

    public function test_correct_answer_is_checked_on_the_server_and_returns_the_explanation(): void
    {
        $this->seed(HeroChallengeSeeder::class);

        $challenge = HeroChallenge::query()->with('options')->orderBy('sort_order')->orderBy('id')->firstOrFail();
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

        $challenge = HeroChallenge::query()->with('options')->orderBy('sort_order')->orderBy('id')->firstOrFail();
        $wrongOption = $challenge->options->firstWhere('is_correct', false);

        $this->post(route('hero-challenges.answer', $challenge), [
            'option_id' => $wrongOption->id,
        ])
            ->assertRedirect(route('home', ['challenge_id' => $challenge->id]) . '#hero-challenge')
            ->assertSessionHas('hero_challenge_result.correct', false)
            ->assertSessionMissing('hero_challenge_result.explanation');
    }

    public function test_json_answer_returns_the_compact_contract_with_at_most_two_queries(): void
    {
        $this->seed(HeroChallengeSeeder::class);

        $challenges = HeroChallenge::query()
            ->with('options')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(3)
            ->get();
        $challenge = $challenges->firstOrFail();
        $nextChallenge = $challenges->get(1);
        $correctOption = $challenge->options->firstWhere('is_correct', true);

        Cache::forget('public.hero-challenges.active.v1');
        $queryCount = 0;
        DB::listen(static function (QueryExecuted $query) use (&$queryCount): void {
            $queryCount++;
        });

        $this->postJson(route('hero-challenges.answer', $challenge->id), [
            'option_id' => $correctOption->id,
        ])
            ->assertOk()
            ->assertExactJson([
                'correct' => true,
                'explanation' => $challenge->explanation,
                'next' => $nextChallenge?->id,
            ]);

        $this->assertLessThanOrEqual(2, $queryCount);
    }

    public function test_json_wrong_answer_never_returns_explanation_or_next_challenge(): void
    {
        $this->seed(HeroChallengeSeeder::class);

        $challenge = HeroChallenge::query()->with('options')->orderBy('sort_order')->orderBy('id')->firstOrFail();
        $wrongOption = $challenge->options->firstWhere('is_correct', false);

        $this->postJson(route('hero-challenges.answer', $challenge->id), [
            'option_id' => $wrongOption->id,
        ])
            ->assertOk()
            ->assertJsonPath('correct', false)
            ->assertJsonPath('explanation', null)
            ->assertJsonPath('next', null);
    }

    public function test_next_challenge_endpoint_returns_only_public_challenge_fields(): void
    {
        $this->seed(HeroChallengeSeeder::class);

        $challenge = HeroChallenge::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->skip(1)
            ->firstOrFail();

        $this->getJson(route('hero-challenges.show', $challenge->id))
            ->assertOk()
            ->assertJsonPath('id', $challenge->id)
            ->assertJsonPath('title', $challenge->title)
            ->assertJsonCount(4, 'options')
            ->assertJsonMissingPath('options.0.is_correct');
    }

    public function test_an_option_from_another_challenge_cannot_be_submitted(): void
    {
        $this->seed(HeroChallengeSeeder::class);

        $challenge = HeroChallenge::query()->with('options')->orderBy('sort_order')->orderBy('id')->firstOrFail();
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
