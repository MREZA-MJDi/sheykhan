<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicHomeSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_and_primary_public_destinations_are_reachable(): void
    {
        $home = $this->get(route('home'));

        $home->assertOk()
            ->assertSee('آموزش خوب')
            ->assertDontSee('home-banner-slider');

        $this->get(route('courses.index'))->assertOk();
        $this->get(route('teachers.index'))->assertOk();
        $this->get(route('blog.index'))->assertOk();
        $this->get(route('store.index'))->assertOk();
    }

    public function test_unknown_public_path_returns_not_found(): void
    {
        $this->get('/this-page-definitely-does-not-exist')->assertNotFound();
    }

    public function test_missing_course_returns_not_found(): void
    {
        $this->get('/courses/999999999')->assertNotFound();
    }
}
