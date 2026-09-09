<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('pages.home');
})->name('home');


Route::get('/courses', function () {
    return view('pages.courses.index');
})->name('courses.index');


Route::get('/courses/{course}', function (string $course) {
    return view('pages.courses.show', [
        'course' => $course,
    ]);
})->name('courses.show');


Route::get('/teachers', function () {
    return view('pages.teachers.index');
})->name('teachers.index');


Route::get('/teachers/{teacher}', function (string $teacher) {
    return view('pages.teachers.show', [
        'teacher' => $teacher,
    ]);
})->name('teachers.show');


/*
|--------------------------------------------------------------------------
| Frontend / UI Test
|--------------------------------------------------------------------------
*/

Route::get('/ui-test', function () {
    return view('pages.ui-test');
})->name('ui.test');