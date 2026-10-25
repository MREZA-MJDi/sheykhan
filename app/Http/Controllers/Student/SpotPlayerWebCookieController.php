<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\StudentAccessService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

final class SpotPlayerWebCookieController extends Controller
{
    public function __invoke(Request $request, Course $course): Response
    {
        abort_unless(
            app(StudentAccessService::class)->course($request->user(), $course),
            404
        );
        abort_if(blank($course->spotplayer_course_id), 404);

        $currentCookie = (string) $request->cookie('X', '');
        $validCurrentCookie = preg_match('/\A[a-f0-9]{36,512}\z/i', $currentCookie) === 1;
        $expiresAt = $validCurrentCookie
            ? (int) hexdec(substr($currentCookie, 24, 12))
            : 0;

        $refreshRequested = $request->query->has('f')
            || ! $validCurrentCookie
            || $expiresAt <= (int) floor(microtime(true) * 1000);

        if (! $refreshRequested) {
            return response('', 204)
                ->header('Cache-Control', 'no-store, private');
        }

        try {
            $upstreamRequest = Http::timeout(8)->withoutRedirecting();

            if ($validCurrentCookie) {
                $upstreamRequest = $upstreamRequest->withHeaders([
                    'Cookie' => 'X=' . $currentCookie,
                ]);
            }

            $upstream = $upstreamRequest->head('https://app.spotplayer.ir/');
        } catch (Throwable $exception) {
            report($exception);

            return $this->unavailable();
        }

        $headers = $upstream->headers();
        $setCookies = $headers['Set-Cookie'] ?? $headers['set-cookie'] ?? [];
        $setCookies = is_array($setCookies) ? $setCookies : [$setCookies];
        $newCookie = null;

        foreach ($setCookies as $setCookie) {
            if (preg_match('/(?:^|,\s*)X=([a-f0-9]+)(?:;|$)/i', (string) $setCookie, $matches) === 1) {
                $newCookie = $matches[1];
                break;
            }
        }

        if (! $upstream->successful() || ! is_string($newCookie) || $newCookie === '') {
            return $this->unavailable();
        }

        // SpotPlayer's official web-player API needs this cookie readable by its
        // JavaScript running in our page, so HttpOnly must remain false. Never log it.
        $cookie = cookie(
            'X',
            $newCookie,
            576000,
            '/',
            null,
            app()->environment('production'),
            false,
            false,
            'Lax'
        );

        return response('', 204)
            ->withCookie($cookie)
            ->header('Cache-Control', 'no-store, private')
            ->header('Pragma', 'no-cache');
    }

    private function unavailable(): Response
    {
        return response('پخش امن موقتاً در دسترس نیست. صفحه را دوباره بارگذاری کنید.', 503)
            ->header('Cache-Control', 'no-store, private')
            ->header('Pragma', 'no-cache');
    }
}
