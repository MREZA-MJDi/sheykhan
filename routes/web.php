<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\Owner\AcademyController as OwnerAcademyController;
use App\Http\Controllers\Owner\CourseController as OwnerCourseController;
use App\Http\Controllers\Owner\CourseMediaController as OwnerCourseMediaController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboard;
use App\Http\Controllers\Owner\PeopleController as OwnerPeopleController;
use App\Http\Controllers\ParentPortal\DashboardController as ParentDashboard;
use App\Http\Controllers\PublicSite\BlogController;
use App\Http\Controllers\PublicSite\CourseController;
use App\Http\Controllers\PublicSite\TeacherController;
use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use App\Http\Controllers\Teacher\AssignmentController as TeacherAssignmentController;
use App\Http\Controllers\Teacher\AssignmentSubmissionController as TeacherAssignmentSubmissionController;
use App\Http\Controllers\Teacher\AttendanceController as TeacherAttendanceController;
use App\Http\Controllers\Teacher\ClassroomController as TeacherClassroomController;
use App\Http\Controllers\Teacher\CourseController as TeacherCourseController;
use App\Http\Controllers\Teacher\CourseLearningProgressController;
use App\Http\Controllers\Teacher\CourseMediaController as TeacherCourseMediaController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboard;
use App\Http\Controllers\Teacher\ExamAttemptController as TeacherExamAttemptController;
use App\Http\Controllers\Teacher\ExamController as TeacherExamController;
use App\Http\Controllers\Teacher\LiveClassController as TeacherLiveClassController;
use Illuminate\Support\Facades\Route;

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

    Route::get('/academy/{academy}/edit', [OwnerAcademyController::class, 'edit'])->name('academy.edit');
    Route::patch('/academy/{academy}', [OwnerAcademyController::class, 'update'])->name('academy.update');

    Route::get('/academy/{academy}/people', [OwnerPeopleController::class, 'index'])->name('people.index');
    Route::post('/academy/{academy}/people/assign-teacher', [OwnerPeopleController::class, 'assignTeacher'])
        ->middleware('permission:teachers.manage')
        ->name('people.assign-teacher');

    Route::get('/courses', [OwnerCourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/create', [OwnerCourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [OwnerCourseController::class, 'store'])->name('courses.store');
    Route::get('/courses/{course}/edit', [OwnerCourseController::class, 'edit'])->name('courses.edit');
    Route::patch('/courses/{course}', [OwnerCourseController::class, 'update'])->name('courses.update');
    Route::post('/courses/{course}/media', [OwnerCourseMediaController::class, 'store'])->name('courses.media.store');
    Route::delete('/courses/{course}/media/{media}', [OwnerCourseMediaController::class, 'destroy'])->name('courses.media.destroy');
});

Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', TeacherDashboard::class)->name('dashboard');

    Route::get('/courses', [TeacherCourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/create', [TeacherCourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [TeacherCourseController::class, 'store'])->name('courses.store');
    Route::get('/courses/{course}/edit', [TeacherCourseController::class, 'edit'])->name('courses.edit');
    Route::patch('/courses/{course}', [TeacherCourseController::class, 'update'])->name('courses.update');
    Route::post('/courses/{course}/media', [TeacherCourseMediaController::class, 'store'])->name('courses.media.store');
    Route::delete('/courses/{course}/media/{media}', [TeacherCourseMediaController::class, 'destroy'])->name('courses.media.destroy');
    Route::get('/courses/{course}/progress', CourseLearningProgressController::class)->name('courses.progress');

    Route::get('/classrooms', [TeacherClassroomController::class, 'index'])->name('classrooms.index');
    Route::get('/classrooms/create', [TeacherClassroomController::class, 'create'])->name('classrooms.create');
    Route::post('/classrooms', [TeacherClassroomController::class, 'store'])->name('classrooms.store');
    Route::get('/classrooms/{classroom}/attendance', [TeacherAttendanceController::class, 'edit'])->name('classrooms.attendance.edit');
    Route::post('/classrooms/{classroom}/attendance', [TeacherAttendanceController::class, 'store'])->name('classrooms.attendance.store');

    Route::get('/students', fn (\App\Services\TeacherWorkspaceService $workspace) => view('teacher.students.index', [
        'students' => $workspace->students(request()->user()),
    ]))->name('students.index');

    Route::get('/assignments', [TeacherAssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/create', [TeacherAssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/assignments', [TeacherAssignmentController::class, 'store'])->name('assignments.store');
    Route::patch('/assignments/{assignment}/submissions/{submission}', [TeacherAssignmentSubmissionController::class, 'update'])
        ->middleware('permission:assignments.manage')
        ->name('assignments.submissions.update');

    Route::get('/exams', [TeacherExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/create', [TeacherExamController::class, 'create'])->name('exams.create');
    Route::post('/exams', [TeacherExamController::class, 'store'])->name('exams.store');
    Route::post('/exam-attempts/{attempt}/grade', [TeacherExamAttemptController::class, 'grade'])
        ->middleware('permission:exams.manage')
        ->name('exam-attempts.grade');

    Route::get('/live-classes', [TeacherLiveClassController::class, 'index'])->name('live-classes.index');
    Route::get('/live-classes/create', [TeacherLiveClassController::class, 'create'])->name('live-classes.create');
    Route::post('/live-classes', [TeacherLiveClassController::class, 'store'])->name('live-classes.store');
});

Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', StudentDashboard::class)->name('dashboard');
});

Route::middleware(['auth', 'role:parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('/dashboard', ParentDashboard::class)->name('dashboard');
});
