<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Website
|--------------------------------------------------------------------------
*/

// Home
Route::view('/', 'pages.home')
    ->name('home');

// About
Route::view('/about', 'pages.about')
    ->name('about');

// Courses
Route::prefix('courses')
    ->name('courses.')
    ->group(function () {
        Route::view('/', 'pages.courses.index')
            ->name('index');

        Route::get('/{course}', function (string $course) {
            return view('pages.courses.show', [
                'course' => $course,
            ]);
        })->name('show');
    });

// Teachers
Route::prefix('teachers')
    ->name('teachers.')
    ->group(function () {
        Route::view('/', 'pages.teachers.index')
            ->name('index');

        Route::get('/{teacher}', function (string $teacher) {
            return view('pages.teachers.show', [
                'teacher' => $teacher,
            ]);
        })->name('show');
    });

// Blog
Route::prefix('blog')
    ->name('blog.')
    ->group(function () {
        Route::view('/', 'pages.blog.index')
            ->name('index');

        Route::get('/{slug}', function (string $slug) {
            return view('pages.blog.show', [
                'slug' => $slug,
            ]);
        })->name('show');
    });


/*
|--------------------------------------------------------------------------
| Authentication UI
|--------------------------------------------------------------------------
|
| فعلاً فقط Viewها را برای تست Frontend نمایش می‌دهیم.
| بعداً این Routeها به Controller / Auth flow واقعی متصل می‌شوند.
|
*/

Route::prefix('auth')
    ->name('auth.')
    ->group(function () {
        Route::view('/login', 'pages.auth.login')
            ->name('login');

        Route::view('/register', 'pages.auth.register')
            ->name('register');

        Route::view('/forgot-password', 'pages.auth.forgot-password')
            ->name('forgot-password');

        Route::view('/verify-otp', 'pages.auth.verify-otp')
            ->name('verify-otp');
    });


/*
|--------------------------------------------------------------------------
| Frontend / UI Test
|--------------------------------------------------------------------------
*/

Route::view('/ui-test', 'pages.design-system')
    ->name('ui.test');
