<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForcePasswordChangeController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\Owner\AcademyController as OwnerAcademyController;
use App\Http\Controllers\Owner\ClassroomController as OwnerClassroomController;
use App\Http\Controllers\Owner\CourseController as OwnerCourseController;
use App\Http\Controllers\Owner\CourseMediaController as OwnerCourseMediaController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboard;
use App\Http\Controllers\Owner\AttendanceController as OwnerAttendanceController;
use App\Http\Controllers\Owner\MeetingController as OwnerMeetingController;
use App\Http\Controllers\Owner\AssignmentController as OwnerAssignmentController;
use App\Http\Controllers\Owner\ScheduleController as OwnerScheduleController;
use App\Http\Controllers\Owner\EnrollmentController as OwnerEnrollmentController;
use App\Http\Controllers\Owner\PeopleController as OwnerPeopleController;
use App\Http\Controllers\Owner\ReportController as OwnerReportController;
use App\Http\Controllers\ParentPortal\DashboardController as ParentDashboard;
use App\Http\Controllers\ParentPortal\ResourceController as ParentResourceController;
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
use App\Http\Controllers\Student\StudentMessageController;
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
use App\Http\Controllers\Teacher\StudentMessageController as TeacherStudentMessageController;
use App\Http\Controllers\Teacher\ProfileController as TeacherProfileController;
use App\Http\Controllers\Teacher\StudentController as TeacherStudentController;
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

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/account/change-password', [ForcePasswordChangeController::class, 'show'])
        ->name('password.change.show');
    Route::put('/account/change-password', [ForcePasswordChangeController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.change.update');
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

        Route::get('/checkout/course/{course}', [\App\Http\Controllers\Commerce\CourseCheckoutController::class, 'show'])->name('checkout.course.show');
    Route::post('/checkout/course/{course}', [\App\Http\Controllers\Commerce\CourseCheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.course.store');
    Route::get('/library', [\App\Http\Controllers\Commerce\LibraryController::class, 'index'])->name('library.index');

Route::get('/checkout/{product:slug}', [\App\Http\Controllers\Commerce\CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/{product:slug}', [\App\Http\Controllers\Commerce\CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');
    Route::get('/orders/{order}', [\App\Http\Controllers\Commerce\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/payment-proof', [\App\Http\Controllers\Commerce\OrderController::class, 'uploadProof'])->middleware('throttle:5,1')->name('orders.proof.store');
    Route::get('/orders/{order}/files/{file}/view', [\App\Http\Controllers\Commerce\OrderController::class, 'viewFile'])->middleware('throttle:60,1')->name('orders.files.view');
    Route::get('/orders/{order}/files/{file}/pages/{page}', [\App\Http\Controllers\Commerce\OrderController::class, 'viewFilePage'])->whereNumber('page')->middleware('throttle:180,1')->name('orders.files.pages');
    Route::get('/orders/{order}/files/{file}/download', [\App\Http\Controllers\Commerce\OrderController::class, 'download'])->middleware('throttle:20,1')->name('orders.files.download');
});

Route::middleware(['auth','active','role:academy-owner','owner.cache.invalidate'])->prefix('owner')->name('owner.')->scopeBindings()->group(function () {
    Route::get('/spotplayer', [\App\Http\Controllers\Owner\SpotPlayerSettingsController::class, 'edit'])->middleware('permission:courses.manage')->name('spotplayer.edit');
    Route::put('/spotplayer', [\App\Http\Controllers\Owner\SpotPlayerSettingsController::class, 'update'])->middleware(['permission:courses.manage','throttle:10,1'])->name('spotplayer.update');
    Route::get('/dashboard', OwnerDashboard::class)->middleware('permission:dashboard.view')->name('dashboard');
    Route::get('/academy/{academy}/edit', [OwnerAcademyController::class, 'edit'])->middleware('permission:academy.view')->name('academy.edit');
    Route::patch('/academy/{academy}', [OwnerAcademyController::class, 'update'])->middleware('permission:academy.manage')->name('academy.update');
    Route::get('/academy/{academy}/people', [OwnerPeopleController::class, 'index'])->middleware('permission:academy.view')->name('people.index');
    Route::get('/academy/{academy}/people/student-options', [OwnerPeopleController::class, 'studentOptions'])->middleware(['permission:academy.view','throttle:60,1'])->name('people.student-options');
    Route::get('/academy/{academy}/attendance', [OwnerAttendanceController::class, 'index'])->middleware('permission:attendance.view')->name('attendance.index');
    Route::patch('/academy/{academy}/attendance/{attendance}', [\App\Http\Controllers\Owner\AttendanceController::class, 'update'])->middleware('permission:attendance.manage')->withoutScopedBindings()->name('attendance.update');
    Route::get('/academy/{academy}/meetings', [OwnerMeetingController::class, 'index'])->middleware('permission:live_classes.view')->name('meetings.index');
    Route::post('/academy/{academy}/meetings', [OwnerMeetingController::class, 'store'])->middleware(['permission:live_classes.manage','throttle:20,1'])->name('meetings.store');
    Route::patch('/academy/{academy}/meetings/{meeting}/status', [OwnerMeetingController::class, 'updateStatus'])->middleware('permission:live_classes.manage')->name('meetings.status');
    Route::get('/academy/{academy}/schedules', [OwnerScheduleController::class, 'index'])->middleware('permission:live_classes.view')->name('schedules.index');
    Route::post('/academy/{academy}/classrooms/{classroom}/schedules', [OwnerScheduleController::class, 'store'])->middleware(['permission:live_classes.manage','throttle:20,1'])->name('schedules.store');
    Route::delete('/academy/{academy}/classrooms/{classroom}/schedules/{schedule}', [OwnerScheduleController::class, 'destroy'])->middleware('permission:live_classes.manage')->name('schedules.destroy');
    Route::get('/academy/{academy}/assignments/create', [OwnerAssignmentController::class, 'create'])->middleware('permission:courses.manage')->name('assignments.create');
    Route::post('/academy/{academy}/assignments', [OwnerAssignmentController::class, 'store'])->middleware(['permission:courses.manage','throttle:20,1'])->name('assignments.store');
    Route::get('/academy/{academy}/assignments', [OwnerAssignmentController::class, 'index'])->middleware('permission:courses.view')->name('assignments.index');
    Route::get('/academy/{academy}/assignments/{assignment}', [OwnerAssignmentController::class, 'show'])->middleware('permission:courses.view')->name('assignments.show');
    Route::patch('/academy/{academy}/assignments/{assignment}/submissions/{submission}', [OwnerAssignmentController::class, 'grade'])->middleware('permission:courses.manage')->name('assignments.grade');
    Route::get('/academy/{academy}/classrooms', [OwnerClassroomController::class, 'index'])->middleware('permission:classrooms.view')->name('classrooms.index');
    Route::get('/academy/{academy}/classrooms/create', [OwnerClassroomController::class, 'create'])->middleware('permission:classrooms.manage')->name('classrooms.create');
    Route::post('/academy/{academy}/classrooms', [OwnerClassroomController::class, 'store'])->middleware('permission:classrooms.manage')->name('classrooms.store');
    Route::get('/academy/{academy}/classrooms/{classroom}', [OwnerClassroomController::class, 'show'])->middleware('permission:classrooms.view')->name('classrooms.show');
    Route::get('/academy/{academy}/classrooms/{classroom}/edit', [OwnerClassroomController::class, 'edit'])->middleware('permission:classrooms.manage')->name('classrooms.edit');
    Route::patch('/academy/{academy}/classrooms/{classroom}', [OwnerClassroomController::class, 'update'])->middleware('permission:classrooms.manage')->name('classrooms.update');
    Route::post('/academy/{academy}/people/legacy-students', [OwnerPeopleController::class, 'onboardLegacyStudent'])->middleware('permission:onboarding.manage')->name('people.legacy-students.store');
    Route::post('/academy/{academy}/people/students', [OwnerPeopleController::class, 'storeStudentAccount'])->middleware(['permission:onboarding.manage','throttle:10,1'])->name('people.store-student');
    Route::post('/academy/{academy}/people/store-teacher', [OwnerPeopleController::class, 'storeTeacher'])->middleware('permission:teachers.manage')->name('people.store-teacher');
    Route::post('/academy/{academy}/people/store-parent', [OwnerPeopleController::class, 'storeParent'])->middleware(['permission:parents.manage','throttle:10,1'])->name('people.store-parent');
    Route::patch('/academy/{academy}/people/teachers/{teacher}/archive', [OwnerPeopleController::class, 'archiveTeacher'])->middleware('permission:teachers.manage')->name('people.archive-teacher');
    Route::patch('/academy/{academy}/people/teachers/{teacher}/restore', [OwnerPeopleController::class, 'restoreTeacher'])->middleware('permission:teachers.manage')->name('people.restore-teacher');
    Route::patch('/academy/{academy}/people/teachers/{teacher}/visibility', [OwnerPeopleController::class, 'updateTeacherVisibility'])->middleware('permission:teachers.manage')->name('people.teacher-visibility');
    Route::post('/academy/{academy}/people/assign-teacher', [OwnerPeopleController::class, 'assignTeacher'])->middleware('permission:teachers.manage')->name('people.assign-teacher');
    Route::post('/academy/{academy}/people/assign-class-teacher', [OwnerPeopleController::class, 'assignClassTeacher'])->middleware(['permission:teachers.manage','throttle:20,1'])->name('people.assign-class-teacher');
    Route::post('/academy/{academy}/people/enroll-student', [OwnerEnrollmentController::class, 'store'])->middleware('permission:enrollments.manage')->name('people.enroll-student');
    Route::get('/courses', [OwnerCourseController::class, 'index'])->middleware('permission:courses.view')->name('courses.index');
    Route::get('/courses/create', [OwnerCourseController::class, 'create'])->middleware('permission:courses.manage')->name('courses.create');
    Route::post('/courses', [OwnerCourseController::class, 'store'])->middleware('permission:courses.manage')->name('courses.store');
    Route::get('/courses/{course}', [OwnerCourseController::class, 'show'])->middleware('permission:courses.view')->name('courses.show');
    Route::get('/courses/{course}/edit', [OwnerCourseController::class, 'edit'])->middleware('permission:courses.manage')->name('courses.edit');
    Route::patch('/courses/{course}', [OwnerCourseController::class, 'update'])->middleware('permission:courses.manage')->name('courses.update');
    Route::post('/courses/{course}/media', [OwnerCourseMediaController::class, 'store'])->middleware('permission:media.upload')->name('courses.media.store');
    Route::delete('/courses/{course}/media/{media}', [OwnerCourseMediaController::class, 'destroy'])->middleware('permission:media.manage')->name('courses.media.destroy');
    Route::get('/academy/{academy}/communications', [\App\Http\Controllers\Owner\AcademyMessageController::class, 'index'])->middleware('permission:parents.view')->withoutScopedBindings()->name('communications.index');
    Route::post('/academy/{academy}/communications', [\App\Http\Controllers\Owner\AcademyMessageController::class, 'store'])->middleware(['permission:parents.manage','throttle:10,1'])->withoutScopedBindings()->name('communications.store');
    Route::get('/academy/{academy}/messages', [\App\Http\Controllers\Owner\ParentMessageController::class, 'index'])->middleware('permission:parents.view')->withoutScopedBindings()->name('messages.index');
    Route::patch('/academy/{academy}/messages/{message}/reply', [\App\Http\Controllers\Owner\ParentMessageController::class, 'reply'])->middleware('permission:parents.manage')->withoutScopedBindings()->name('messages.reply');
    Route::get('/academy/{academy}/counseling', [\App\Http\Controllers\Owner\CounselingRequestController::class, 'index'])->middleware('permission:parents.view')->withoutScopedBindings()->name('counseling.index');
    Route::patch('/academy/{academy}/counseling/{counselingRequest}', [\App\Http\Controllers\Owner\CounselingRequestController::class, 'update'])->middleware('permission:parents.manage')->withoutScopedBindings()->name('counseling.update');
    Route::get('/academy/{academy}/exams/create', [\App\Http\Controllers\Owner\ExamController::class, 'create'])->middleware('permission:exams.manage')->withoutScopedBindings()->name('exams.create');
    Route::post('/academy/{academy}/exams', [\App\Http\Controllers\Owner\ExamController::class, 'store'])->middleware(['permission:exams.manage','throttle:10,1'])->withoutScopedBindings()->name('exams.store');
    Route::get('/academy/{academy}/exams/{exam}/edit', [\App\Http\Controllers\Owner\ExamController::class, 'edit'])->whereNumber('exam')->middleware('permission:exams.manage')->withoutScopedBindings()->name('exams.edit');
    Route::patch('/academy/{academy}/exams/{exam}', [\App\Http\Controllers\Owner\ExamController::class, 'update'])->whereNumber('exam')->middleware('permission:exams.manage')->withoutScopedBindings()->name('exams.update');
    Route::get('/academy/{academy}/exams', [\App\Http\Controllers\Owner\ExamController::class, 'index'])->middleware('permission:exams.view')->withoutScopedBindings()->name('exams.index');
    Route::get('/academy/{academy}/exams/{exam}', [\App\Http\Controllers\Owner\ExamController::class, 'show'])->whereNumber('exam')->middleware('permission:exams.view')->withoutScopedBindings()->name('exams.show');
    Route::patch('/academy/{academy}/exams/{exam}/status', [\App\Http\Controllers\Owner\ExamController::class, 'updateStatus'])->middleware('permission:exams.manage')->withoutScopedBindings()->name('exams.status');
    Route::patch('/academy/{academy}/exams/{exam}/attempts/{attempt}/grade', [\App\Http\Controllers\Owner\ExamController::class, 'gradeAttempt'])->middleware(['permission:exams.manage','throttle:20,1'])->withoutScopedBindings()->name('exams.attempts.grade');
    Route::get('/academy/{academy}/recordings', [\App\Http\Controllers\Owner\RecordingController::class, 'index'])->middleware('permission:live_classes.view')->withoutScopedBindings()->name('recordings.index');
    Route::post('/academy/{academy}/recordings/{liveClass}', [\App\Http\Controllers\Owner\RecordingController::class, 'upload'])->middleware(['permission:live_classes.manage','throttle:5,1'])->withoutScopedBindings()->name('recordings.upload');
    Route::patch('/academy/{academy}/recordings/{liveClass}', [\App\Http\Controllers\Owner\RecordingController::class, 'updatePublication'])->middleware('permission:live_classes.manage')->withoutScopedBindings()->name('recordings.update');
    Route::get('/academy/{academy}/teacher-payments', [\App\Http\Controllers\Owner\TeacherPaymentController::class, 'index'])->middleware('permission:teacher_payments.view')->withoutScopedBindings()->name('teacher-payments.index');
    Route::post('/academy/{academy}/teacher-payments', [\App\Http\Controllers\Owner\TeacherPaymentController::class, 'store'])->middleware(['permission:teacher_payments.manage', 'throttle:10,1'])->withoutScopedBindings()->name('teacher-payments.store');
    Route::patch('/academy/{academy}/teacher-payments/{payment}/status', [\App\Http\Controllers\Owner\TeacherPaymentController::class, 'updateStatus'])->middleware('permission:teacher_payments.manage')->withoutScopedBindings()->name('teacher-payments.status');

    Route::get('/reports', [OwnerReportController::class, 'index'])->middleware('permission:reports.view')->name('reports.index');
    Route::get('/academy/{academy}/resources', [\App\Http\Controllers\Owner\LearningResourceController::class, 'index'])->middleware('permission:resources.view')->name('resources.index');
    Route::post('/academy/{academy}/resources', [\App\Http\Controllers\Owner\LearningResourceController::class, 'store'])->middleware(['permission:resources.manage','throttle:10,1'])->name('resources.store');
    Route::get('/academy/{academy}/resources/{resource}/edit', [\App\Http\Controllers\Owner\LearningResourceController::class, 'edit'])->whereNumber('resource')->middleware('permission:resources.manage')->withoutScopedBindings()->name('resources.edit');
    Route::patch('/academy/{academy}/resources/{resource}', [\App\Http\Controllers\Owner\LearningResourceController::class, 'update'])->whereNumber('resource')->middleware(['permission:resources.manage','throttle:10,1'])->withoutScopedBindings()->name('resources.update');
    Route::patch('/academy/{academy}/resources/{resource}/archive', [\App\Http\Controllers\Owner\LearningResourceController::class, 'archive'])->whereNumber('resource')->middleware('permission:resources.manage')->withoutScopedBindings()->name('resources.archive');
    Route::patch('/academy/{academy}/resources/{resource}/restore', [\App\Http\Controllers\Owner\LearningResourceController::class, 'restore'])->whereNumber('resource')->middleware('permission:resources.manage')->withoutScopedBindings()->name('resources.restore');
    Route::get('/academy/{academy}/products', [\App\Http\Controllers\Owner\ProductManagementController::class, 'index'])->middleware('permission:products.view')->name('products.index');
    Route::post('/academy/{academy}/products', [\App\Http\Controllers\Owner\ProductManagementController::class, 'store'])->middleware(['permission:products.manage','throttle:10,1'])->name('products.store');
    Route::get('/academy/{academy}/products/{product}/edit', [\App\Http\Controllers\Owner\ProductManagementController::class, 'edit'])->whereNumber('product')->middleware('permission:products.manage')->withoutScopedBindings()->name('products.edit');
    Route::patch('/academy/{academy}/products/{product}', [\App\Http\Controllers\Owner\ProductManagementController::class, 'update'])->whereNumber('product')->middleware(['permission:products.manage','throttle:10,1'])->withoutScopedBindings()->name('products.update');
    Route::patch('/academy/{academy}/products/{product}/publish', [\App\Http\Controllers\Owner\ProductManagementController::class, 'publish'])->middleware('permission:products.manage')->name('products.publish');
    Route::patch('/academy/{academy}/products/{product}/archive', [\App\Http\Controllers\Owner\ProductManagementController::class, 'archive'])->middleware('permission:products.manage')->name('products.archive');
    Route::get('/academy/{academy}/orders', [\App\Http\Controllers\Owner\OrderReviewController::class, 'index'])->middleware('permission:orders.view')->withoutScopedBindings()->name('orders.index');
    Route::get('/academy/{academy}/orders/{order}/payments/{payment}/proof', [\App\Http\Controllers\Owner\OrderReviewController::class, 'proof'])->middleware('permission:orders.view')->withoutScopedBindings()->name('orders.proof');
    Route::post('/academy/{academy}/orders/{order}/payments/{payment}/confirm', [\App\Http\Controllers\Owner\OrderReviewController::class, 'confirm'])->middleware(['permission:orders.manage','throttle:20,1'])->withoutScopedBindings()->name('orders.confirm');
    Route::post('/academy/{academy}/orders/{order}/payments/{payment}/reject', [\App\Http\Controllers\Owner\OrderReviewController::class, 'reject'])->middleware(['permission:orders.manage','throttle:20,1'])->withoutScopedBindings()->name('orders.reject');
    Route::get('/legal-documents', [\App\Http\Controllers\Owner\LegalDocumentController::class, 'index'])->middleware('permission:legal.manage')->name('legal-documents.index');
    Route::post('/legal-documents', [\App\Http\Controllers\Owner\LegalDocumentController::class, 'store'])->middleware(['permission:legal.manage','throttle:10,1'])->name('legal-documents.store');
    Route::patch('/legal-documents/{document}/deactivate', [\App\Http\Controllers\Owner\LegalDocumentController::class, 'deactivate'])->middleware('permission:legal.manage')->name('legal-documents.deactivate');
    Route::get('/content-rights', [\App\Http\Controllers\Owner\ContentRightController::class, 'index'])->middleware('permission:legal.manage')->name('content-rights.index');
    Route::post('/content-rights', [\App\Http\Controllers\Owner\ContentRightController::class, 'store'])->middleware(['permission:legal.manage','throttle:10,1'])->name('content-rights.store');
    Route::get('/content-rights/{contentRight}/edit', [\App\Http\Controllers\Owner\ContentRightController::class, 'edit'])->middleware('permission:legal.manage')->name('content-rights.edit');
    Route::patch('/content-rights/{contentRight}', [\App\Http\Controllers\Owner\ContentRightController::class, 'update'])->middleware(['permission:legal.manage','throttle:10,1'])->name('content-rights.update');
    Route::patch('/content-rights/{contentRight}/activate', [\App\Http\Controllers\Owner\ContentRightController::class, 'activate'])->middleware(['permission:legal.manage','throttle:10,1'])->name('content-rights.activate');
    Route::patch('/content-rights/{contentRight}/revoke', [\App\Http\Controllers\Owner\ContentRightController::class, 'revoke'])->middleware(['permission:legal.manage','throttle:10,1'])->name('content-rights.revoke');
    Route::get('/content-rights/{contentRight}/evidence', [\App\Http\Controllers\Owner\ContentRightController::class, 'evidence'])->middleware('permission:legal.manage')->name('content-rights.evidence');

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
    Route::get('/students', [TeacherStudentController::class, 'index'])->middleware('permission:students.view')->name('students.index');
    Route::get('/assignments', [TeacherAssignmentController::class, 'index'])->middleware('permission:assignments.view')->name('assignments.index');
    Route::get('/assignments/create', [TeacherAssignmentController::class, 'create'])->middleware('permission:assignments.manage')->name('assignments.create');
    Route::post('/assignments', [TeacherAssignmentController::class, 'store'])->middleware('permission:assignments.manage')->name('assignments.store');
    Route::get('/assignments/{assignment}/submissions', [TeacherAssignmentController::class, 'submissions'])->middleware('permission:assignments.view')->name('assignments.submissions');
    Route::patch('/assignments/{assignment}/submissions/{submission}', [TeacherAssignmentSubmissionController::class, 'update'])->middleware('permission:assignments.manage')->name('assignments.submissions.update');
    Route::get('/exams', [TeacherExamController::class, 'index'])->middleware('permission:exams.view')->name('exams.index');
    Route::get('/exams/create', [TeacherExamController::class, 'create'])->middleware('permission:exams.manage')->name('exams.create');
    Route::post('/exams', [TeacherExamController::class, 'store'])->middleware('permission:exams.manage')->name('exams.store');
    Route::get('/exams/{exam}/edit', [TeacherExamController::class, 'edit'])->middleware('permission:exams.manage')->name('exams.edit');
    Route::patch('/exams/{exam}', [TeacherExamController::class, 'update'])->middleware('permission:exams.manage')->name('exams.update');
    Route::get('/exams/{exam}/attempts', [TeacherExamController::class, 'attempts'])->middleware('permission:exams.view')->name('exams.attempts');
    Route::post('/exam-attempts/{attempt}/grade', [TeacherExamAttemptController::class, 'grade'])->middleware('permission:exams.manage')->name('exam-attempts.grade');
    Route::patch('/exam-attempts/{attempt}/grade-manual', [TeacherExamAttemptController::class, 'gradeManual'])->middleware('permission:exams.manage')->name('exam-attempts.grade-manual');
    Route::get('/messages', [\App\Http\Controllers\Teacher\ParentMessageController::class, 'index'])->middleware('permission:parent_messages.view')->name('messages.index');
    Route::patch('/messages/{message}/reply', [\App\Http\Controllers\Teacher\ParentMessageController::class, 'reply'])->middleware(['permission:parent_messages.manage','throttle:10,1'])->name('messages.reply');
    Route::get('/direct-messages', [\App\Http\Controllers\Teacher\DirectMessageController::class, 'index'])->middleware('permission:parent_messages.manage')->name('direct-messages.index');
    Route::post('/direct-messages', [\App\Http\Controllers\Teacher\DirectMessageController::class, 'store'])->middleware(['permission:parent_messages.manage','throttle:10,1'])->name('direct-messages.store');
    Route::get('/communications', [\App\Http\Controllers\Teacher\AcademyMessageController::class, 'index'])->middleware('permission:parent_messages.manage')->name('communications.index');
    Route::post('/communications', [\App\Http\Controllers\Teacher\AcademyMessageController::class, 'store'])->middleware(['permission:parent_messages.manage','throttle:10,1'])->name('communications.store');
    Route::patch('/direct-messages/{message}/reply', [\App\Http\Controllers\Teacher\DirectMessageController::class, 'replyToParentMessage'])->middleware(['permission:parent_messages.manage','throttle:10,1'])->name('direct-messages.reply');
    Route::get('/student-messages', [TeacherStudentMessageController::class, 'index'])->middleware('permission:student_messages.view')->name('student-messages.index');
    Route::patch('/student-messages/{message}/reply', [TeacherStudentMessageController::class, 'reply'])->middleware(['permission:student_messages.manage','throttle:10,1'])->name('student-messages.reply');
    Route::patch('/student-messages/{message}/close', [TeacherStudentMessageController::class, 'close'])->middleware(['permission:student_messages.manage','throttle:10,1'])->name('student-messages.close');
    Route::get('/meetings', [\App\Http\Controllers\Teacher\MeetingController::class, 'index'])->middleware('permission:live_classes.view')->name('meetings.index');
    Route::post('/meetings/proposals', [\App\Http\Controllers\Teacher\MeetingController::class, 'propose'])->middleware(['permission:live_classes.manage','throttle:10,1'])->name('meetings.proposals.store');
    Route::patch('/meetings/{meeting}/status', [\App\Http\Controllers\Teacher\MeetingController::class, 'updateStatus'])->middleware('permission:live_classes.manage')->name('meetings.status');
    Route::get('/counseling-requests', [\App\Http\Controllers\Teacher\CounselingRequestController::class, 'index'])->middleware('permission:live_classes.view')->name('counseling-requests.index');
    Route::post('/counseling-requests/{counselingRequest}/propose', [\App\Http\Controllers\Teacher\CounselingRequestController::class, 'propose'])->whereNumber('counselingRequest')->middleware(['permission:live_classes.manage','throttle:10,1'])->name('counseling-requests.propose');
    Route::get('/payments', [\App\Http\Controllers\Teacher\PaymentController::class, 'index'])->middleware('permission:teacher_payments.view')->name('payments.index');
    Route::get('/live-classes', [TeacherLiveClassController::class, 'index'])->middleware('permission:live_classes.view')->name('live-classes.index');
    Route::get('/live-classes/create', [TeacherLiveClassController::class, 'create'])->middleware('permission:live_classes.manage')->name('live-classes.create');
    Route::post('/live-classes', [TeacherLiveClassController::class, 'store'])->middleware('permission:live_classes.manage')->name('live-classes.store');
    Route::post('/live-classes/{liveClass}/recording', [TeacherLiveClassController::class, 'uploadRecording'])->middleware(['permission:live_classes.manage','throttle:5,1'])->name('live-classes.recording.store');
});

Route::middleware(['auth','active','role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', StudentDashboard::class)->middleware('permission:dashboard.view')->name('dashboard');

    Route::get('/messages', [StudentMessageController::class, 'index'])->middleware('permission:student_messages.view')->name('messages.index');
    Route::post('/messages', [StudentMessageController::class, 'store'])->middleware(['permission:student_messages.manage','throttle:5,1'])->name('messages.store');
    Route::patch('/messages/from-teacher/{message}/reply', [StudentMessageController::class, 'replyToTeacherMessage'])->middleware(['permission:student_messages.manage','throttle:10,1'])->name('teacher-messages.reply');

    Route::get('/courses', [StudentCourseController::class, 'index'])->middleware('permission:courses.view')->name('courses.index');
    Route::get('/courses/{course}', [StudentCourseController::class, 'show'])->middleware('permission:courses.view')->name('courses.show');
    Route::post('/courses/{course}/spotplayer-license', [\App\Http\Controllers\Student\SpotPlayerLicenseController::class, 'store'])->middleware(['permission:courses.view','throttle:5,1'])->name('courses.spotplayer-license');
    Route::get('/courses/{course}/spotplayer-cookie', \App\Http\Controllers\Student\SpotPlayerWebCookieController::class)->middleware(['permission:courses.view','throttle:60,1'])->name('courses.spotplayer-cookie');
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
    Route::get('/weekly-schedule/{schedule}/join', [StudentLiveClassController::class, 'joinSchedule'])->middleware(['permission:live_classes.view','throttle:20,1'])->name('schedule.join');

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
    Route::get('/messages', [\App\Http\Controllers\ParentPortal\MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages', [\App\Http\Controllers\ParentPortal\MessageController::class, 'store'])->middleware('throttle:5,1')->name('messages.store');
    Route::post('/messages/teacher', [\App\Http\Controllers\ParentPortal\MessageController::class, 'storeToTeacher'])->middleware('throttle:10,1')->name('messages.teacher.store');
    Route::patch('/teacher-messages/{message}/reply', [\App\Http\Controllers\ParentPortal\MessageController::class, 'replyToTeacherMessage'])->middleware('throttle:10,1')->name('teacher-messages.reply');
    Route::get('/meetings', [\App\Http\Controllers\ParentPortal\MeetingController::class, 'index'])->name('meetings.index');
    Route::get('/counseling', [\App\Http\Controllers\ParentPortal\CounselingRequestController::class, 'index'])->name('counseling.index');
    Route::post('/counseling', [\App\Http\Controllers\ParentPortal\CounselingRequestController::class, 'store'])->middleware('throttle:5,1')->name('counseling.store');
    Route::get('/attendance', [\App\Http\Controllers\ParentPortal\AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/results', [\App\Http\Controllers\ParentPortal\ResultController::class, 'index'])->name('results.index');
    Route::get('/resources', [ParentResourceController::class, 'index'])->name('resources.index');
    Route::get('/resources/{resource}/view', [ParentResourceController::class, 'view'])->whereNumber('resource')->name('resources.view');
});

Route::middleware(['auth','active','role:seo-specialist','permission:seo.manage'])
    ->prefix('owner')
    ->name('owner.')
    ->group(function () {
        // Public website content and page-level controls are restricted to the dedicated SEO role.
        // Academy operations (members, classes, courses, commerce, messaging and finance) stay owner-scoped.
        Route::get('/academy/{academy}/home-banners', [\App\Http\Controllers\Owner\HomeBannerController::class, 'edit'])->name('academy.banners.edit');
        Route::patch('/academy/{academy}/home-banners', [\App\Http\Controllers\Owner\HomeBannerController::class, 'update'])->middleware('throttle:10,1')->name('academy.banners.update');
        Route::get('/website', \App\Http\Controllers\Owner\WebsiteController::class)->name('website.index');
        Route::get('/website/hero', [\App\Http\Controllers\Owner\HomeHeroController::class, 'edit'])->name('website.hero.edit');
        Route::patch('/website/hero', [\App\Http\Controllers\Owner\HomeHeroController::class, 'update'])->middleware('throttle:10,1')->name('website.hero.update');

        Route::get('/content', [\App\Http\Controllers\Owner\ContentController::class, 'index'])->name('content.index');
        Route::get('/content/create', [\App\Http\Controllers\Owner\ContentController::class, 'create'])->name('content.create');
        Route::post('/content', [\App\Http\Controllers\Owner\ContentController::class, 'store'])->middleware('throttle:10,1')->name('content.store');
        Route::get('/content/{content}/edit', [\App\Http\Controllers\Owner\ContentController::class, 'edit'])->whereNumber('content')->name('content.edit');
        Route::patch('/content/{content}', [\App\Http\Controllers\Owner\ContentController::class, 'update'])->middleware('throttle:10,1')->name('content.update');

        Route::get('/blog', [\App\Http\Controllers\Owner\BlogController::class, 'index'])->name('blog.index');
        Route::get('/blog/create', [\App\Http\Controllers\Owner\BlogController::class, 'create'])->name('blog.create');
        Route::post('/blog', [\App\Http\Controllers\Owner\BlogController::class, 'store'])->middleware('throttle:10,1')->name('blog.store');
        Route::get('/blog/{post}/edit', [\App\Http\Controllers\Owner\BlogController::class, 'edit'])->whereNumber('post')->name('blog.edit');
        Route::patch('/blog/{post}', [\App\Http\Controllers\Owner\BlogController::class, 'update'])->middleware('throttle:10,1')->name('blog.update');

        Route::get('/academy/{academy}/showcase', [\App\Http\Controllers\Owner\ShowcaseController::class, 'index'])->name('showcase.index');
        Route::post('/academy/{academy}/achievements', [\App\Http\Controllers\Owner\ShowcaseController::class, 'storeAchievement'])->middleware('throttle:10,1')->name('achievements.store');
        Route::patch('/academy/{academy}/achievements/{achievement}/withdraw', [\App\Http\Controllers\Owner\ShowcaseController::class, 'withdrawAchievement'])->name('achievements.withdraw');
        Route::post('/academy/{academy}/testimonials', [\App\Http\Controllers\Owner\ShowcaseController::class, 'storeTestimonial'])->middleware('throttle:10,1')->name('testimonials.store');
        Route::patch('/academy/{academy}/testimonials/{testimonial}/withdraw', [\App\Http\Controllers\Owner\ShowcaseController::class, 'withdrawTestimonial'])->name('testimonials.withdraw');

        // SEO is permission-scoped so a dedicated specialist does not need academy-owner access.
        Route::get('/seo', [\App\Http\Controllers\Owner\SeoController::class, 'index'])->name('seo.index');
        Route::get('/seo/keywords/template', [\App\Http\Controllers\Owner\SeoKeywordController::class, 'template'])->name('seo.keywords.template');
        Route::get('/seo/keywords/export', [\App\Http\Controllers\Owner\SeoKeywordController::class, 'export'])->name('seo.keywords.export');
        Route::post('/seo/keywords/import', [\App\Http\Controllers\Owner\SeoKeywordController::class, 'import'])->middleware('throttle:10,1')->name('seo.keywords.import');
        Route::get('/seo/keywords', [\App\Http\Controllers\Owner\SeoKeywordController::class, 'index'])->name('seo.keywords.index');
        Route::get('/seo/keywords/create', [\App\Http\Controllers\Owner\SeoKeywordController::class, 'create'])->name('seo.keywords.create');
        Route::post('/seo/keywords', [\App\Http\Controllers\Owner\SeoKeywordController::class, 'store'])->middleware('throttle:30,1')->name('seo.keywords.store');
        Route::get('/seo/keywords/{keyword}/edit', [\App\Http\Controllers\Owner\SeoKeywordController::class, 'edit'])->whereNumber('keyword')->name('seo.keywords.edit');
        Route::patch('/seo/keywords/{keyword}', [\App\Http\Controllers\Owner\SeoKeywordController::class, 'update'])->whereNumber('keyword')->name('seo.keywords.update');
        Route::post('/seo/keywords/{keyword}/rankings', [\App\Http\Controllers\Owner\SeoKeywordController::class, 'storeRanking'])->whereNumber('keyword')->middleware('throttle:60,1')->name('seo.keywords.rankings.store');
        Route::delete('/seo/keywords/{keyword}', [\App\Http\Controllers\Owner\SeoKeywordController::class, 'destroy'])->whereNumber('keyword')->name('seo.keywords.destroy');
        Route::get('/seo/{type}/{id}/edit', [\App\Http\Controllers\Owner\SeoController::class, 'edit'])->whereIn('type', ['academy', 'course', 'content', 'blog', 'product', 'teacher'])->whereNumber('id')->name('seo.edit');
        Route::patch('/seo/{type}/{id}', [\App\Http\Controllers\Owner\SeoController::class, 'update'])->whereIn('type', ['academy', 'course', 'content', 'blog', 'product', 'teacher'])->whereNumber('id')->name('seo.update');
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
