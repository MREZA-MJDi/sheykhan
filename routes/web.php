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
use App\Http\Controllers\PublicSite\ProductController;
use App\Http\Controllers\PublicSite\SeoController as PublicSeoController;
use App\Http\Controllers\PublicSite\AcademyContentController;
use App\Http\Controllers\Student\AchievementController as StudentAchievementController;
use App\Http\Controllers\Student\AssignmentController as StudentAssignmentController;
use App\Http\Controllers\Student\AttendanceController as StudentAttendanceController;
use App\Http\Controllers\Student\CourseController as StudentCourseController;
use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use App\Http\Controllers\Student\ExamController as StudentExamController;
use App\Http\Controllers\Student\LessonController as StudentLessonController;
use App\Http\Controllers\Student\LessonProgressController as StudentLessonProgressController;
use App\Http\Controllers\Student\LiveClassController as StudentLiveClassController;
use App\Http\Controllers\Student\NoteController as StudentNoteController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;
use App\Http\Controllers\Student\ResourceController as StudentResourceController;
use App\Http\Controllers\Student\ResultController as StudentResultController;
use App\Http\Controllers\Student\SecurityController as StudentSecurityController;
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
use App\Http\Controllers\Teacher\ProfileController as TeacherProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/courses/{course}/lessons/{lesson}/preview', [CourseController::class, 'preview'])->name('courses.lessons.preview');
Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
Route::get('/teachers/{teacher}', [TeacherController::class, 'show'])->name('teachers.show');
Route::get('/store', [StoreController::class, 'index'])->name('store.index');
Route::get('/store/{product:slug}', [ProductController::class, 'show'])->name('store.product.show');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/robots.txt', [PublicSeoController::class, 'robots'])->name('seo.robots');
Route::get('/sitemap.xml', [PublicSeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/academy/{academy:slug}/content/{content:slug}', [AcademyContentController::class, 'show'])->name('academy.content.show');

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
    Route::get('/media/{media}/view', [MediaController::class, 'view'])
        ->middleware('permission:media.view')
        ->name('media.view');
    Route::get('/media/{media}/download', [MediaController::class, 'download'])
        ->middleware('permission:media.download')
        ->name('media.download');
});

Route::middleware(['auth','active','role:academy-owner'])->prefix('owner')->name('owner.')->scopeBindings()->group(function () {
    Route::get('/dashboard', OwnerDashboard::class)->middleware('permission:dashboard.view')->name('dashboard');
    Route::get('/academy/{academy}/edit', [OwnerAcademyController::class, 'edit'])->middleware('permission:academy.view')->name('academy.edit');
    Route::patch('/academy/{academy}', [OwnerAcademyController::class, 'update'])->middleware('permission:academy.manage')->name('academy.update');
    Route::get('/academy/{academy}/home-banners', [\App\Http\Controllers\Owner\HomeBannerController::class, 'edit'])->middleware('permission:academy.view')->name('academy.banners.edit');
    Route::patch('/academy/{academy}/home-banners', [\App\Http\Controllers\Owner\HomeBannerController::class, 'update'])->middleware('permission:academy.manage')->name('academy.banners.update');
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
    Route::get('/website', \App\Http\Controllers\Owner\WebsiteController::class)->middleware('permission:academy.view')->name('website.index');
    Route::get('/seo', [\App\Http\Controllers\Owner\SeoController::class, 'index'])->middleware('permission:seo.manage')->name('seo.index');
    Route::get('/seo/{type}/{id}/edit', [\App\Http\Controllers\Owner\SeoController::class, 'edit'])->middleware('permission:seo.manage')->name('seo.edit');
    Route::patch('/seo/{type}/{id}', [\App\Http\Controllers\Owner\SeoController::class, 'update'])->middleware('permission:seo.manage')->name('seo.update');
    Route::get('/content', [\App\Http\Controllers\Owner\ContentController::class, 'index'])->middleware('permission:content.manage')->name('content.index');
    Route::get('/content/create', [\App\Http\Controllers\Owner\ContentController::class, 'create'])->middleware('permission:content.manage')->name('content.create');
    Route::post('/content', [\App\Http\Controllers\Owner\ContentController::class, 'store'])->middleware('permission:content.manage')->name('content.store');
    Route::get('/content/{content}/edit', [\App\Http\Controllers\Owner\ContentController::class, 'edit'])->middleware('permission:content.manage')->name('content.edit');
    Route::patch('/content/{content}', [\App\Http\Controllers\Owner\ContentController::class, 'update'])->middleware('permission:content.manage')->name('content.update');
    Route::get('/blog', [\App\Http\Controllers\Owner\BlogController::class, 'index'])->middleware('permission:blog.manage')->name('blog.index');
    Route::get('/blog/create', [\App\Http\Controllers\Owner\BlogController::class, 'create'])->middleware('permission:blog.manage')->name('blog.create');
    Route::post('/blog', [\App\Http\Controllers\Owner\BlogController::class, 'store'])->middleware('permission:blog.manage')->name('blog.store');
    Route::get('/blog/{post}/edit', [\App\Http\Controllers\Owner\BlogController::class, 'edit'])->middleware('permission:blog.manage')->name('blog.edit');
    Route::patch('/blog/{post}', [\App\Http\Controllers\Owner\BlogController::class, 'update'])->middleware('permission:blog.manage')->name('blog.update');
    Route::get('/finance', [\App\Http\Controllers\Owner\FinanceController::class, 'index'])->middleware('permission:finance.view')->name('finance.index');
});

Route::middleware(['auth','active','role:teacher','active-teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', TeacherDashboard::class)->middleware('permission:dashboard.view')->name('dashboard');
    Route::get('/profile', [TeacherProfileController::class, 'edit'])->middleware('permission:profile.view')->name('profile.edit');
    Route::patch('/profile', [TeacherProfileController::class, 'update'])->middleware(['permission:profile.manage','throttle:10,1'])->name('profile.update');
    Route::get('/courses', [TeacherCourseController::class, 'index'])->middleware('permission:courses.view')->name('courses.index');
    Route::get('/courses/create', [TeacherCourseController::class, 'create'])->middleware('permission:courses.manage')->name('courses.create');
    Route::post('/courses', [TeacherCourseController::class, 'store'])->middleware('permission:courses.manage')->name('courses.store');
    Route::get('/courses/{course}/edit', [TeacherCourseController::class, 'edit'])->middleware('permission:courses.manage')->name('courses.edit');
    Route::patch('/courses/{course}', [TeacherCourseController::class, 'update'])->middleware('permission:courses.manage')->name('courses.update');
    Route::post('/courses/{course}/media', [TeacherCourseMediaController::class, 'store'])->middleware('permission:media.upload')->name('courses.media.store');
    Route::delete('/courses/{course}/media/{media}', [TeacherCourseMediaController::class, 'destroy'])->middleware('permission:media.manage')->name('courses.media.destroy');
    Route::get('/courses/{course}/progress', CourseLearningProgressController::class)->middleware('permission:courses.view')->name('courses.progress');
    Route::get('/courses/{course}/content', [TeacherLessonController::class, 'index'])->middleware('permission:lessons.view')->name('courses.content');
    Route::post('/courses/{course}/sections', [TeacherLessonController::class, 'storeSection'])->middleware('permission:lessons.manage')->name('courses.sections.store');
    Route::post('/lessons', [TeacherLessonController::class, 'store'])->middleware('permission:lessons.manage')->name('lessons.store');
    Route::patch('/lessons/{lesson}', [TeacherLessonController::class, 'update'])->middleware('permission:lessons.manage')->name('lessons.update');
    Route::post('/lessons/{lesson}/media', [TeacherLessonMediaController::class, 'store'])->middleware('permission:media.upload')->name('lessons.media.store');
    Route::get('/classrooms', [TeacherClassroomController::class, 'index'])->middleware('permission:classrooms.view')->name('classrooms.index');
    Route::get('/classrooms/create', [TeacherClassroomController::class, 'create'])->middleware('permission:classrooms.manage')->name('classrooms.create');
    Route::post('/classrooms', [TeacherClassroomController::class, 'store'])->middleware('permission:classrooms.manage')->name('classrooms.store');
    Route::get('/attendance', [TeacherAttendanceController::class, 'index'])->middleware('permission:attendance.view')->name('attendance.index');
    Route::get('/classrooms/{classroom}/attendance', [TeacherAttendanceController::class, 'edit'])->middleware('permission:attendance.view')->name('classrooms.attendance.edit');
    Route::post('/classrooms/{classroom}/attendance', [TeacherAttendanceController::class, 'store'])->middleware('permission:attendance.manage')->name('classrooms.attendance.store');
    Route::get('/schedule', [App\Http\Controllers\Teacher\ScheduleController::class, 'index'])->middleware('permission:live_classes.view')->name('schedule.index');
    Route::post('/schedule', [App\Http\Controllers\Teacher\ScheduleController::class, 'store'])->middleware('permission:live_classes.manage')->name('schedule.store');
    Route::delete('/schedule/{schedule}', [App\Http\Controllers\Teacher\ScheduleController::class, 'destroy'])->middleware('permission:live_classes.manage')->name('schedule.destroy');
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

    Route::get('/courses', [StudentCourseController::class, 'index'])->middleware('permission:courses.view')->name('courses.index');
    Route::get('/courses/{course}', [StudentCourseController::class, 'show'])->middleware('permission:courses.view')->name('courses.show');
    Route::get('/lessons/{lesson}', [StudentLessonController::class, 'show'])->middleware('permission:lessons.view')->name('lessons.show');
    Route::patch('/lessons/{lesson}/progress', [StudentLessonProgressController::class, 'update'])->middleware(['permission:lessons.progress','throttle:30,1'])->name('lessons.progress.update');

    Route::get('/assignments', [StudentAssignmentController::class, 'index'])->middleware('permission:assignments.view')->name('assignments.index');
    Route::get('/assignments/{assignment}', [StudentAssignmentController::class, 'show'])->middleware('permission:assignments.view')->name('assignments.show');
    Route::post('/assignments/{assignment}/submit', [StudentAssignmentController::class, 'submit'])->middleware(['permission:assignments.submit','throttle:10,1'])->name('assignments.submit');

    Route::get('/exams', [StudentExamController::class, 'index'])->middleware('permission:exams.view')->name('exams.index');
    Route::get('/exams/{exam}', [StudentExamController::class, 'show'])->middleware('permission:exams.view')->name('exams.show');
    Route::post('/exams/{exam}/start', [StudentExamController::class, 'start'])->middleware(['permission:exams.attempt','throttle:10,1'])->name('exams.start');
    Route::get('/exams/{exam}/attempts/{attempt}', [StudentExamController::class, 'attempt'])->middleware('permission:exams.view')->name('exams.attempt');
    Route::post('/exam-attempts/{attempt}/submit', [StudentExamController::class, 'submit'])->middleware(['permission:exams.attempt','throttle:10,1'])->name('exam-attempts.submit');
    Route::get('/exams/{exam}/result', [StudentExamController::class, 'result'])->middleware('permission:exams.view')->name('exams.result');

    Route::get('/live-classes', [StudentLiveClassController::class, 'index'])->middleware('permission:live_classes.view')->name('live-classes.index');
    Route::get('/live-classes/{liveClass}/join', [StudentLiveClassController::class, 'join'])->middleware(['permission:live_classes.view','throttle:20,1'])->name('live-classes.join');

    Route::get('/attendance', [StudentAttendanceController::class, 'index'])->middleware('permission:attendance.view')->name('attendance.index');
    Route::get('/results', [StudentResultController::class, 'index'])->middleware('permission:results.view')->name('results.index');
    Route::get('/achievements', [StudentAchievementController::class, 'index'])->middleware('permission:achievements.view')->name('achievements.index');

    Route::get('/notes', [StudentNoteController::class, 'index'])->middleware('permission:notes.view')->name('notes.index');
    Route::post('/lessons/{lesson}/notes', [StudentNoteController::class, 'store'])->middleware(['permission:notes.manage','throttle:20,1'])->name('lessons.notes.store');
    Route::delete('/notes/{note}', [StudentNoteController::class, 'destroy'])->middleware('permission:notes.manage')->name('notes.destroy');

    Route::get('/profile', [StudentProfileController::class, 'edit'])->middleware('permission:profile.view')->name('profile.edit');
    Route::patch('/profile', [StudentProfileController::class, 'update'])->middleware(['permission:profile.manage','throttle:10,1'])->name('profile.update');
    Route::patch('/profile/password', [StudentSecurityController::class, 'updatePassword'])->middleware(['permission:profile.security','throttle:6,1'])->name('profile.password.update');

    Route::get('/resources', [StudentResourceController::class, 'index'])->middleware('permission:resources.view')->name('resources.index');
    Route::get('/resources/{resource}/view', [StudentResourceController::class, 'view'])->middleware('permission:resources.view')->name('resources.view');
    Route::get('/resources/{resource}/download', [StudentResourceController::class, 'download'])->middleware('permission:resources.view')->name('resources.download');
});
Route::middleware(['auth','active','role:parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('/dashboard', ParentDashboard::class)->middleware('permission:dashboard.view')->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Final fallback
|--------------------------------------------------------------------------
| Never let an unknown web URL fall through to an internal error page.
| This must remain the last route in the file.
*/
Route::fallback(function () {
    if (request()->expectsJson()) {
        return response()->json([
            'message' => 'صفحه یا رکورد موردنظر پیدا نشد.',
        ], 404);
    }

    return response()->view('errors.404', [], 404);
});
