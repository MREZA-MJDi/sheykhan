<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TiamirIntroTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_uses_the_hero_without_a_blocking_tiamir_intro_layer(): void
    {
        $this->withoutVite();
        $this->seed();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-home-hero', false)
            ->assertSee('sheykhan-home-hero', false)
            ->assertDontSee('data-tiamir-intro', false)
            ->assertDontSee('RMMAJIDI DEV')
            ->assertDontSee('BREAK THE STATIC')
            ->assertDontSee('SHIP THE SYSTEM');

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertDontSee('data-tiamir-intro', false);
    }
}
