<?php

use AppHttpControllersAuthController;
use AppHttpControllersDashboardRedirectController;
use AppHttpControllersHomeController;
use AppHttpControllersMediaController;
use AppHttpControllersOwnerDashboardController as OwnerDashboard;
use AppHttpControllersParentPortalDashboardController as ParentDashboard;
use AppHttpControllersPublicSiteBlogController;
use AppHttpControllersPublicSiteCourseController;
use AppHttpControllersPublicSiteTeacherController;
use AppHttpControllersStudentDashboardController as StudentDashboard;
use AppHttpControllersTeacherDashboardController as TeacherDashboard;
use IlluminateSupportFacadesRoute;

Route::get('/', HomeController::class)->name('home');

Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login.store');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:6,1')
        ->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardRedirectController::class)->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/media/{media}/download', [MediaController::class, 'download'])
        ->name('media.download');
});

Route::middleware(['auth', 'role:academy-owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', OwnerDashboard::class)->name('dashboard');
});

Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', TeacherDashboard::class)->name('dashboard');
});

Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', StudentDashboard::class)->name('dashboard');
});

Route::middleware(['auth', 'role:parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('/dashboard', ParentDashboard::class)->name('dashboard');
});
