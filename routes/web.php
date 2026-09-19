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
use App\Http\Controllers\Teacher\LessonController as TeacherLessonController;
use App\Http\Controllers\Teacher\LessonMediaController as TeacherLessonMediaController;
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
    Route::get('/academy/{academy}/classrooms', [\App\Http\Controllers\Owner\ClassroomController::class, 'index'])->name('classrooms.index');
    Route::get('/academy/{academy}/classrooms/create', [\App\Http\Controllers\Owner\ClassroomController::class, 'create'])->name('classrooms.create');
    Route::post('/academy/{academy}/classrooms', [\App\Http\Controllers\Owner\ClassroomController::class, 'store'])->name('classrooms.store');
    Route::get('/academy/{academy}/classrooms/{classroom}/edit', [\App\Http\Controllers\Owner\ClassroomController::class, 'edit'])->name('classrooms.edit');
    Route::patch('/academy/{academy}/classrooms/{classroom}', [\App\Http\Controllers\Owner\ClassroomController::class, 'update'])->name('classrooms.update');
    Route::patch('/academy/{academy}/classrooms/{classroom}/status', [\App\Http\Controllers\Owner\ClassroomController::class, 'updateStatus'])->name('classrooms.status');
    Route::get('/reports', [\App\Http\Controllers\Owner\ReportController::class, 'index'])->name('reports.index');
    Route::post('/academy/{academy}/people/store-teacher', [OwnerPeopleController::class, 'storeTeacher'])
        ->middleware('permission:teachers.manage')
        ->name('people.store-teacher');
    Route::post('/academy/{academy}/people/assign-teacher', [OwnerPeopleController::class, 'assignTeacher'])
        ->middleware('permission:teachers.manage')
        ->name('people.assign-teacher');
    Route::delete('/academy/{academy}/people/teachers/{teacher}/courses/{course}', [OwnerPeopleController::class, 'detachTeacher'])
        ->middleware('permission:teachers.manage')
        ->name('people.detach-teacher');
    Route::post('/academy/{academy}/people/enroll-student', [OwnerPeopleController::class, 'enrollStudent'])
        ->middleware('permission:enrollments.manage')
        ->name('people.enroll-student');

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
    Route::get('/courses/{course}/content', [TeacherLessonController::class, 'index'])->name('courses.content');
    Route::post('/courses/{course}/sections', [\App\Http\Controllers\Teacher\CourseSectionController::class, 'store'])->name('courses.sections.store');
    Route::post('/courses/{course}/sections/reorder', [\App\Http\Controllers\Teacher\CourseSectionController::class, 'reorder'])->name('courses.sections.reorder');
    Route::post('/lessons', [TeacherLessonController::class, 'store'])->name('lessons.store');
    Route::patch('/lessons/{lesson}', [TeacherLessonController::class, 'update'])->name('lessons.update');
    Route::delete('/lessons/{lesson}', [TeacherLessonController::class, 'destroy'])->name('lessons.destroy');
    Route::post('/lessons/{lesson}/media', [TeacherLessonMediaController::class, 'store'])->name('lessons.media.store');
    Route::delete('/lessons/{lesson}/media/{media}', [TeacherLessonMediaController::class, 'destroy'])->name('lessons.media.destroy');
    Route::get('/lessons/{lesson}/media/{media}/stream', [TeacherLessonMediaController::class, 'stream'])->name('lessons.media.stream');
    Route::patch('/sections/{section}', [\App\Http\Controllers\Teacher\CourseSectionController::class, 'update'])->name('sections.update');
    Route::delete('/sections/{section}', [\App\Http\Controllers\Teacher\CourseSectionController::class, 'destroy'])->name('sections.destroy');

    Route::get('/classrooms', [TeacherClassroomController::class, 'index'])->name('classrooms.index');
    Route::get('/classrooms/create', [TeacherClassroomController::class, 'create'])->name('classrooms.create');
    Route::post('/classrooms', [TeacherClassroomController::class, 'store'])->name('classrooms.store');
    Route::get('/classrooms/{classroom}', [TeacherClassroomController::class, 'show'])->name('classrooms.show');
    Route::get('/classrooms/{classroom}/attendance', [TeacherAttendanceController::class, 'edit'])->name('classrooms.attendance.edit');
    Route::get('/schedule', [\App\Http\Controllers\Teacher\ScheduleController::class, 'index'])->name('schedule.index');
    Route::post('/schedule', [\App\Http\Controllers\Teacher\ScheduleController::class, 'store'])->name('schedule.store');
    Route::post('/classrooms/{classroom}/attendance', [TeacherAttendanceController::class, 'store'])->name('classrooms.attendance.store');

    Route::get('/students', fn (\App\Services\TeacherWorkspaceService $workspace) => view('teacher.students.index', [
        'students' => $workspace->students(request()->user()),
    ]))->name('students.index');
    Route::get('/students/{student}', [\App\Http\Controllers\Teacher\StudentController::class, 'show'])
        ->name('students.show');

    Route::get('/assignments', [TeacherAssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/create', [TeacherAssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/assignments', [TeacherAssignmentController::class, 'store'])->name('assignments.store');
    Route::get('/assignments/{assignment}/submissions', [TeacherAssignmentController::class, 'submissions'])->name('assignments.submissions');
    Route::patch('/assignments/{assignment}/submissions/{submission}', [TeacherAssignmentSubmissionController::class, 'update'])
        ->middleware('permission:assignments.manage')
        ->name('assignments.submissions.update');

    Route::get('/exams', [TeacherExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/create', [TeacherExamController::class, 'create'])->name('exams.create');
    Route::post('/exams', [TeacherExamController::class, 'store'])->name('exams.store');
    Route::get('/exams/{exam}/attempts', [TeacherExamController::class, 'attempts'])->name('exams.attempts');
    Route::post('/exam-attempts/{attempt}/grade', [TeacherExamAttemptController::class, 'grade'])
        ->middleware('permission:exams.manage')
        ->name('exam-attempts.grade');
    Route::patch('/exam-attempts/{attempt}/grade-manual', [TeacherExamAttemptController::class, 'gradeManual'])
        ->middleware('permission:exams.manage')
        ->name('exam-attempts.grade-manual');

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
