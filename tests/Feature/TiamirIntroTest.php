<?php

namespace Tests\Feature;

use Tests\TestCase;

class TiamirIntroTest extends TestCase
{
    public function test_tiamir_intro_is_present_on_home_and_not_on_other_public_pages(): void
    {
        $this->withoutVite();
        $this->seed();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-tiamir-intro', false)
            ->assertSee('RMMAJIDI DEV')
            ->assertSee('BREAK THE STATIC')
            ->assertSee('SHIP THE SYSTEM');

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertDontSee('data-tiamir-intro', false);
    }
}
