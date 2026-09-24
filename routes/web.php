<?php

use AppHttpControllersAuthController;
use AppHttpControllersDashboardRedirectController;
use AppHttpControllersHomeController;
use AppHttpControllersMediaController;
use AppHttpControllersOwnerAcademyController as OwnerAcademyController;
use AppHttpControllersOwnerClassroomController as OwnerClassroomController;
use AppHttpControllersOwnerCourseController as OwnerCourseController;
use AppHttpControllersOwnerCourseMediaController as OwnerCourseMediaController;
use AppHttpControllersOwnerDashboardController as OwnerDashboard;
use AppHttpControllersOwnerEnrollmentController as OwnerEnrollmentController;
use AppHttpControllersOwnerPeopleController as OwnerPeopleController;
use AppHttpControllersOwnerReportController as OwnerReportController;
use AppHttpControllersParentPortalDashboardController as ParentDashboard;
use AppHttpControllersPublicSiteBlogController;
use AppHttpControllersPublicSiteCourseController;
use AppHttpControllersPublicSiteTeacherController;
use AppHttpControllersStudentDashboardController as StudentDashboard;
use AppHttpControllersTeacherAssignmentController as TeacherAssignmentController;
use AppHttpControllersTeacherAssignmentSubmissionController as TeacherAssignmentSubmissionController;
use AppHttpControllersTeacherAttendanceController as TeacherAttendanceController;
use AppHttpControllersTeacherClassroomController as TeacherClassroomController;
use AppHttpControllersTeacherCourseController as TeacherCourseController;
use AppHttpControllersTeacherCourseLearningProgressController;
use AppHttpControllersTeacherCourseMediaController as TeacherCourseMediaController;
use AppHttpControllersTeacherDashboardController as TeacherDashboard;
use AppHttpControllersTeacherExamAttemptController as TeacherExamAttemptController;
use AppHttpControllersTeacherExamController as TeacherExamController;
use AppHttpControllersTeacherLessonController as TeacherLessonController;
use AppHttpControllersTeacherLessonMediaController as TeacherLessonMediaController;
use AppHttpControllersTeacherLiveClassController as TeacherLiveClassController;
use IlluminateSupportFacadesRoute;

Route::get('/', HomeController::class)->name('home');
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardRedirectController::class)->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/media/{media}/download', [MediaController::class, 'download'])->name('media.download');
});

Route::middleware(['auth','role:academy-owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', OwnerDashboard::class)->middleware('permission:dashboard.view')->name('dashboard');
    Route::get('/academy/{academy}/edit', [OwnerAcademyController::class, 'edit'])->middleware('permission:academy.view')->name('academy.edit');
    Route::patch('/academy/{academy}', [OwnerAcademyController::class, 'update'])->middleware('permission:academy.manage')->name('academy.update');
    Route::get('/academy/{academy}/people', [OwnerPeopleController::class, 'index'])->middleware('permission:academy.view')->name('people.index');
    Route::get('/academy/{academy}/classrooms', [OwnerClassroomController::class, 'index'])->middleware('permission:classrooms.view')->name('classrooms.index');
    Route::patch('/academy/{academy}/classrooms/{classroom}', [OwnerClassroomController::class, 'update'])->middleware('permission:classrooms.manage')->name('classrooms.update');
    Route::post('/academy/{academy}/people/store-teacher', [OwnerPeopleController::class, 'storeTeacher'])->middleware('permission:teachers.manage')->name('people.store-teacher');
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

Route::middleware(['auth','role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
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
    Route::post('/lessons', [TeacherLessonController::class, 'store'])->name('lessons.store');
    Route::patch('/lessons/{lesson}', [TeacherLessonController::class, 'update'])->name('lessons.update');
    Route::post('/lessons/{lesson}/media', [TeacherLessonMediaController::class, 'store'])->name('lessons.media.store');
    Route::get('/classrooms', [TeacherClassroomController::class, 'index'])->name('classrooms.index');
    Route::get('/classrooms/create', [TeacherClassroomController::class, 'create'])->name('classrooms.create');
    Route::post('/classrooms', [TeacherClassroomController::class, 'store'])->name('classrooms.store');
    Route::get('/classrooms/{classroom}/attendance', [TeacherAttendanceController::class, 'edit'])->name('classrooms.attendance.edit');
    Route::post('/classrooms/{classroom}/attendance', [TeacherAttendanceController::class, 'store'])->name('classrooms.attendance.store');
    Route::get('/schedule', [AppHttpControllersTeacherScheduleController::class, 'index'])->name('schedule.index');
    Route::post('/schedule', [AppHttpControllersTeacherScheduleController::class, 'store'])->name('schedule.store');
    Route::get('/students', fn (\App\Services\TeacherWorkspaceService $workspace) => view('teacher.students.index', ['students' => $workspace->students(request()->user())]))->name('students.index');
    Route::get('/assignments', [TeacherAssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/create', [TeacherAssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/assignments', [TeacherAssignmentController::class, 'store'])->name('assignments.store');
    Route::get('/assignments/{assignment}/submissions', [TeacherAssignmentController::class, 'submissions'])->name('assignments.submissions');
    Route::patch('/assignments/{assignment}/submissions/{submission}', [TeacherAssignmentSubmissionController::class, 'update'])->middleware('permission:assignments.manage')->name('assignments.submissions.update');
    Route::get('/exams', [TeacherExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/create', [TeacherExamController::class, 'create'])->name('exams.create');
    Route::post('/exams', [TeacherExamController::class, 'store'])->name('exams.store');
    Route::get('/exams/{exam}/attempts', [TeacherExamController::class, 'attempts'])->name('exams.attempts');
    Route::post('/exam-attempts/{attempt}/grade', [TeacherExamAttemptController::class, 'grade'])->middleware('permission:exams.manage')->name('exam-attempts.grade');
    Route::patch('/exam-attempts/{attempt}/grade-manual', [TeacherExamAttemptController::class, 'gradeManual'])->middleware('permission:exams.manage')->name('exam-attempts.grade-manual');
    Route::get('/live-classes', [TeacherLiveClassController::class, 'index'])->name('live-classes.index');
    Route::get('/live-classes/create', [TeacherLiveClassController::class, 'create'])->name('live-classes.create');
    Route::post('/live-classes', [TeacherLiveClassController::class, 'store'])->name('live-classes.store');
});

Route::middleware(['auth','role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', StudentDashboard::class)->name('dashboard');
});
Route::middleware(['auth','role:parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('/dashboard', ParentDashboard::class)->name('dashboard');
});