<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\Owner\AcademyController as OwnerAcademyController;
use App\Http\Controllers\Owner\ClassroomController as OwnerClassroomController;
use App\Http\Controllers\Owner\CourseController as OwnerCourseController;
use App\Http\Controllers\Owner\CourseMediaController as OwnerCourseMediaController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboard;
use App\Http\Controllers\Owner\EnrollmentController as OwnerEnrollmentController;
use App\Http\Controllers\Owner\PeopleController as OwnerPeopleController;
use App\Http\Controllers\Owner\ReportController as OwnerReportController;
use App\Http\Controllers\ParentPortal\DashboardController as ParentDashboard;
use App\Http\Controllers\PublicSite\BlogController;
use App\Http\Controllers\PublicSite\CourseController;
use App\Http\Controllers\PublicSite\TeacherController;
use App\Http\Controllers\PublicSite\StoreController;
use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use App\Http\Controllers\Student\ResourceController as StudentResourceController;
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
use App\Http\Controllers\Teacher\LessonController as TeacherLessonController;
use App\Http\Controllers\Teacher\LessonMediaController as TeacherLessonMediaController;
use App\Http\Controllers\Teacher\LiveClassController as TeacherLiveClassController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
Route::get('/store', [StoreController::class, 'index'])->name('store.index');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('register.store');
});

Route::get('/media/{media}/public', [MediaController::class, 'servePublic'])->name('media.public');

Route::middleware(['auth','active'])->group(function () {
    Route::get('/dashboard', DashboardRedirectController::class)->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/media/{media}/download', [MediaController::class, 'download'])->name('media.download');
});

Route::middleware(['auth','active','role:academy-owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', OwnerDashboard::class)->middleware('permission:dashboard.view')->name('dashboard');
    Route::get('/academy/{academy}/edit', [OwnerAcademyController::class, 'edit'])->middleware('permission:academy.view')->name('academy.edit');
    Route::patch('/academy/{academy}', [OwnerAcademyController::class, 'update'])->middleware('permission:academy.manage')->name('academy.update');
    Route::get('/academy/{academy}/people', [OwnerPeopleController::class, 'index'])->middleware('permission:academy.view')->name('people.index');
    Route::get('/academy/{academy}/classrooms', [OwnerClassroomController::class, 'index'])->middleware('permission:classrooms.view')->name('classrooms.index');
    Route::get('/academy/{academy}/classrooms/create', [OwnerClassroomController::class, 'create'])->middleware('permission:classrooms.manage')->name('classrooms.create');
    Route::post('/academy/{academy}/classrooms', [OwnerClassroomController::class, 'store'])->middleware('permission:classrooms.manage')->name('classrooms.store');
    Route::get('/academy/{academy}/classrooms/{classroom}', [OwnerClassroomController::class, 'show'])->middleware('permission:classrooms.view')->name('classrooms.show');
    Route::get('/academy/{academy}/classrooms/{classroom}/edit', [OwnerClassroomController::class, 'edit'])->middleware('permission:classrooms.manage')->name('classrooms.edit');
    Route::patch('/academy/{academy}/classrooms/{classroom}', [OwnerClassroomController::class, 'update'])->middleware('permission:classrooms.manage')->name('classrooms.update');
    Route::post('/academy/{academy}/people/legacy-students', [OwnerPeopleController::class, 'onboardLegacyStudent'])->middleware('permission:onboarding.manage')->name('people.legacy-students.store');
    Route::post('/academy/{academy}/people/store-teacher', [OwnerPeopleController::class, 'storeTeacher'])->middleware('permission:teachers.manage')->name('people.store-teacher');
    Route::patch('/academy/{academy}/people/teachers/{teacher}/archive', [OwnerPeopleController::class, 'archiveTeacher'])->middleware('permission:teachers.manage')->name('people.archive-teacher');
    Route::patch('/academy/{academy}/people/teachers/{teacher}/restore', [OwnerPeopleController::class, 'restoreTeacher'])->middleware('permission:teachers.manage')->name('people.restore-teacher');
    Route::patch('/academy/{academy}/people/teachers/{teacher}/visibility', [OwnerPeopleController::class, 'updateTeacherVisibility'])->middleware('permission:teachers.manage')->name('people.teacher-visibility');
    Route::post('/academy/{academy}/people/assign-teacher', [OwnerPeopleController::class, 'assignTeacher'])->middleware('permission:teachers.manage')->name('people.assign-teacher');
    Route::post('/academy/{academy}/people/enroll-student', [OwnerEnrollmentController::class, 'store'])->middleware('permission:enrollments.manage')->name('people.enroll-student');
    Route::get('/courses', [OwnerCourseController::class, 'index'])->middleware('permission:courses.view')->name('courses.index');
    Route::get('/courses/create', [OwnerCourseController::class, 'create'])->middleware('permission:courses.manage')->name('courses.create');
    Route::post('/courses', [OwnerCourseController::class, 'store'])->middleware('permission:courses.manage')->name('courses.store');
    Route::get('/courses/{course}', [OwnerCourseController::class, 'show'])->middleware('permission:courses.view')->name('courses.show');
    Route::get('/courses/{course}/edit', [OwnerCourseController::class, 'edit'])->middleware('permission:courses.manage')->name('courses.edit');
    Route::patch('/courses/{course}', [OwnerCourseController::class, 'update'])->middleware('permission:courses.manage')->name('courses.update');
    Route::post('/courses/{course}/media', [OwnerCourseMediaController::class, 'store'])->middleware('permission:media.upload')->name('courses.media.store');
    Route::delete('/courses/{course}/media/{media}', [OwnerCourseMediaController::class, 'destroy'])->middleware('permission:media.manage')->name('courses.media.destroy');
    Route::get('/reports', [OwnerReportController::class, 'index'])->middleware('permission:reports.view')->name('reports.index');
});

Route::middleware(['auth','active','role:teacher','active-teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', TeacherDashboard::class)->middleware('permission:dashboard.view')->name('dashboard');
    Route::get('/courses', [TeacherCourseController::class, 'index'])->middleware('permission:courses.view')->name('courses.index');
    Route::get('/courses/create', [TeacherCourseController::class, 'create'])->middleware('permission:courses.manage')->name('courses.create');
    Route::post('/courses', [TeacherCourseController::class, 'store'])->middleware('permission:courses.manage')->name('courses.store');
    Route::get('/courses/{course}/edit', [TeacherCourseController::class, 'edit'])->middleware('permission:courses.manage')->name('courses.edit');
    Route::patch('/courses/{course}', [TeacherCourseController::class, 'update'])->middleware('permission:courses.manage')->name('courses.update');
    Route::post('/courses/{course}/media', [TeacherCourseMediaController::class, 'store'])->middleware('permission:media.upload')->name('courses.media.store');
    Route::delete('/courses/{course}/media/{media}', [TeacherCourseMediaController::class, 'destroy'])->middleware('permission:media.manage')->name('courses.media.destroy');
    Route::get('/courses/{course}/progress', CourseLearningProgressController::class)->middleware('permission:courses.view')->name('courses.progress');
    Route::get('/courses/{course}/content', [TeacherLessonController::class, 'index'])->middleware('permission:lessons.view')->name('courses.content');
    Route::post('/lessons', [TeacherLessonController::class, 'store'])->middleware('permission:lessons.manage')->name('lessons.store');
    Route::patch('/lessons/{lesson}', [TeacherLessonController::class, 'update'])->middleware('permission:lessons.manage')->name('lessons.update');
    Route::post('/lessons/{lesson}/media', [TeacherLessonMediaController::class, 'store'])->middleware('permission:media.upload')->name('lessons.media.store');
    Route::get('/classrooms', [TeacherClassroomController::class, 'index'])->middleware('permission:classrooms.view')->name('classrooms.index');
    Route::get('/classrooms/create', [TeacherClassroomController::class, 'create'])->middleware('permission:classrooms.view')->name('classrooms.create');
    Route::post('/classrooms', [TeacherClassroomController::class, 'store'])->middleware('permission:classrooms.view')->name('classrooms.store');
    Route::get('/classrooms/{classroom}/attendance', [TeacherAttendanceController::class, 'edit'])->middleware('permission:attendance.view')->name('classrooms.attendance.edit');
    Route::post('/classrooms/{classroom}/attendance', [TeacherAttendanceController::class, 'store'])->middleware('permission:attendance.manage')->name('classrooms.attendance.store');
    Route::get('/schedule', [App\Http\Controllers\Teacher\ScheduleController::class, 'index'])->middleware('permission:live_classes.view')->name('schedule.index');
    Route::post('/schedule', [App\Http\Controllers\Teacher\ScheduleController::class, 'store'])->middleware('permission:live_classes.manage')->name('schedule.store');
    Route::get('/students', function (\App\Services\TeacherWorkspaceService $workspace) {
        $sort = request()->string('sort')->toString() ?: 'name';
        $direction = request()->string('direction')->toString() ?: 'asc';

        return view('teacher.students.index', [
            'students' => $workspace->studentsPaginated(request()->user(), 20, $sort, $direction),
            'studentSort' => in_array($sort, ['name', 'progress', 'assignments', 'exams'], true) ? $sort : 'name',
            'studentDirection' => $direction === 'desc' ? 'desc' : 'asc',
        ]);
    })->middleware('permission:students.view')->name('students.index');
    Route::get('/assignments', [TeacherAssignmentController::class, 'index'])->middleware('permission:assignments.view')->name('assignments.index');
    Route::get('/assignments/create', [TeacherAssignmentController::class, 'create'])->middleware('permission:assignments.manage')->name('assignments.create');
    Route::post('/assignments', [TeacherAssignmentController::class, 'store'])->middleware('permission:assignments.manage')->name('assignments.store');
    Route::get('/assignments/{assignment}/submissions', [TeacherAssignmentController::class, 'submissions'])->middleware('permission:assignments.view')->name('assignments.submissions');
    Route::patch('/assignments/{assignment}/submissions/{submission}', [TeacherAssignmentSubmissionController::class, 'update'])->middleware('permission:assignments.manage')->name('assignments.submissions.update');
    Route::get('/exams', [TeacherExamController::class, 'index'])->middleware('permission:exams.view')->name('exams.index');
    Route::get('/exams/create', [TeacherExamController::class, 'create'])->middleware('permission:exams.manage')->name('exams.create');
    Route::post('/exams', [TeacherExamController::class, 'store'])->middleware('permission:exams.manage')->name('exams.store');
    Route::get('/exams/{exam}/attempts', [TeacherExamController::class, 'attempts'])->middleware('permission:exams.view')->name('exams.attempts');
    Route::post('/exam-attempts/{attempt}/grade', [TeacherExamAttemptController::class, 'grade'])->middleware('permission:exams.manage')->name('exam-attempts.grade');
    Route::patch('/exam-attempts/{attempt}/grade-manual', [TeacherExamAttemptController::class, 'gradeManual'])->middleware('permission:exams.manage')->name('exam-attempts.grade-manual');
    Route::get('/live-classes', [TeacherLiveClassController::class, 'index'])->middleware('permission:live_classes.view')->name('live-classes.index');
    Route::get('/live-classes/create', [TeacherLiveClassController::class, 'create'])->middleware('permission:live_classes.manage')->name('live-classes.create');
    Route::post('/live-classes', [TeacherLiveClassController::class, 'store'])->middleware('permission:live_classes.manage')->name('live-classes.store');
});

Route::middleware(['auth','active','role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', StudentDashboard::class)->middleware('permission:dashboard.view')->name('dashboard');
    Route::get('/resources', [StudentResourceController::class, 'index'])->middleware('permission:dashboard.view')->name('resources.index');
    Route::get('/resources/{resource}/download', [StudentResourceController::class, 'download'])->middleware('permission:dashboard.view')->name('resources.download');
});
Route::middleware(['auth','active','role:parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('/dashboard', ParentDashboard::class)->middleware('permission:dashboard.view')->name('dashboard');
});