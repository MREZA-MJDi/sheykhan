<?php

namespace Tests\Unit;

use App\Models\Media;
use App\Services\CourseVideoStreamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseVideoStreamServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::disk('local')->put('courses/7/lesson.mp4', '0123456789');
    }

    public function test_it_streams_a_video_with_byte_range_support(): void
    {
        $response = app(CourseVideoStreamService::class)->stream(
            $this->video(),
            Request::create('/', 'GET', [], [], [], ['HTTP_RANGE' => 'bytes=2-5']),
        );

        $this->assertSame(206, $response->getStatusCode());
        $this->assertSame('bytes 2-5/10', $response->headers->get('Content-Range'));
        $this->assertSame('4', $response->headers->get('Content-Length'));
        $this->assertSame('bytes', $response->headers->get('Accept-Ranges'));
        $this->assertSame('2345', $this->responseBody($response));
    }

    public function test_it_streams_the_complete_video_when_no_range_is_requested(): void
    {
        $response = app(CourseVideoStreamService::class)->stream(
            $this->video(),
            Request::create('/', 'GET'),
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('10', $response->headers->get('Content-Length'));
        $this->assertSame('0123456789', $this->responseBody($response));
    }

    public function test_it_supports_suffix_byte_ranges(): void
    {
        $response = app(CourseVideoStreamService::class)->stream(
            $this->video(),
            Request::create('/', 'GET', [], [], [], ['HTTP_RANGE' => 'bytes=-3']),
        );

        $this->assertSame(206, $response->getStatusCode());
        $this->assertSame('bytes 7-9/10', $response->headers->get('Content-Range'));
        $this->assertSame('789', $this->responseBody($response));
    }

    public function test_it_rejects_unsatisfiable_byte_ranges(): void
    {
        $response = app(CourseVideoStreamService::class)->stream(
            $this->video(),
            Request::create('/', 'GET', [], [], [], ['HTTP_RANGE' => 'bytes=20-30']),
        );

        $this->assertSame(416, $response->getStatusCode());
        $this->assertSame('bytes */10', $response->headers->get('Content-Range'));
    }

    private function video(): Media
    {
        return new Media([
            'disk' => 'local',
            'path' => 'courses/7/lesson.mp4',
            'original_name' => 'lesson.mp4',
            'mime_type' => 'video/mp4',
            'extension' => 'mp4',
        ]);
    }

    private function responseBody($response): string
    {
        ob_start();
        $response->sendContent();

        return (string) ob_get_clean();
    }
}
