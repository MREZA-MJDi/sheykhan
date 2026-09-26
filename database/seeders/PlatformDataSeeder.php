<?php

namespace Database\Seeders;

use App\Models\Academy;
use App\Models\AcademyContent;
use App\Models\AcademyContentCategory;
use App\Models\AcademicGrade;
use App\Models\AcademicYear;
use App\Models\Achievement;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\ClassSchedule;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseSection;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\LegalConsent;
use App\Models\LegalDocument;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\LearningResource;
use App\Models\LiveClass;
use App\Models\Media;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductDownload;
use App\Models\ProductEntitlement;
use App\Models\ProductFile;
use App\Models\ProtectedFile;
use App\Models\Question;
use App\Models\SeoMeta;
use App\Models\Setting;
use App\Models\StudentOnboarding;
use App\Models\StudentProfile;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PlatformDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $grades = $this->seedReferenceData();
            $year = AcademicYear::query()->where('title', '1405-1406')->firstOrFail();

            $roles = [
                'owner' => 'academy-owner',
                'teacher' => 'teacher',
                'student' => 'student',
                'parent' => 'parent',
            ];

            $owner = $this->user('owner@sheykhan.test', 'مدیر آکادمی شیخان', 'owner', $roles['owner']);
            $teachers = collect([
                $this->user('teacher.math@sheykhan.test', 'سمیه احمدی', 'teacher-math', $roles['teacher']),
                $this->user('teacher.science@sheykhan.test', 'علی رضایی', 'teacher-science', $roles['teacher']),
                $this->user('teacher.gifted@sheykhan.test', 'نگار کریمی', 'teacher-gifted', $roles['teacher']),
            ]);

            $students = collect([
                $this->student('student.armin@sheykhan.test', 'آرین محمدی', 'SH-1001', 'هفتم', $grades['7']),
                $this->student('student.nika@sheykhan.test', 'نیکا حسینی', 'SH-1002', 'هشتم', $grades['8']),
                $this->student('student.parsa@sheykhan.test', 'پارسا رضایی', 'SH-1003', 'نهم', $grades['9']),
                $this->student('student.ava@sheykhan.test', 'آوا محمدی', 'SH-1004', 'هفتم', $grades['7']),
                $this->student('student.matin@sheykhan.test', 'متین احمدی', 'SH-1005', 'هشتم', $grades['8']),
                $this->student('student.tara@sheykhan.test', 'تارا کریمی', 'SH-1006', 'نهم', $grades['9']),
            ]);

            $parents = collect([
                $this->parent('parent.armin@sheykhan.test', 'مریم محمدی', 'مادر', $students->get(0)),
                $this->parent('parent.nika@sheykhan.test', 'حسین حسینی', 'پدر', $students->get(1)),
                $this->parent('parent.parsa@sheykhan.test', 'الهام رضایی', 'مادر', $students->get(2)),
            ]);

            foreach ($teachers as $teacher) {
                $teacher->teacherProfile()->updateOrCreate([], [
                    'bio' => 'مدرس آکادمی شیخان با تمرکز بر آموزش مفهومی و پروژه‌محور.',
                    'specialization' => 'آموزش و حل مسئله',
                    'education' => 'کارشناسی و کارشناسی ارشد مرتبط',
                    'experience_years' => 7,
                    'is_verified' => true,
                ]);
            }

            $academy = Academy::firstOrCreate(
                ['slug' => 'sheykhan-academy'],
                [
                    'owner_id' => $owner->id,
                    'name' => 'آکادمی شیخان',
                    'code' => 'SHK-001',
                    'description' => 'آکادمی آموزشی شیخان؛ آموزش تخصصی، کلاس آنلاین، آزمون و محتوای آموزشی.',
                    'phone' => '02191000000',
                    'email' => 'academy@sheykhan.test',
                    'address' => 'تهران',
                    'city' => 'تهران',
                    'province' => 'تهران',
                    'website' => 'https://sheykhan.test',
                    'status' => 'active',
                ]
            );

            $academy->users()->syncWithoutDetaching([
                $owner->id => ['role' => 'owner', 'status' => 'active', 'joined_at' => now()->subMonths(4)],
            ]);
            foreach ($teachers as $teacher) {
                $academy->users()->syncWithoutDetaching([
                    $teacher->id => ['role' => 'teacher', 'status' => 'active', 'joined_at' => now()->subMonths(3)],
                ]);
            }
            foreach ($students as $student) {
                $academy->users()->syncWithoutDetaching([
                    $student->id => ['role' => 'student', 'status' => 'active', 'joined_at' => now()->subMonths(2)],
                ]);
            }
            foreach ($parents as $parent) {
                $academy->users()->syncWithoutDetaching([
                    $parent->id => ['role' => 'parent', 'status' => 'active', 'joined_at' => now()->subMonths(2)],
                ]);
            }

            $courses = collect();
            $courseDefinitions = [
                ['slug' => 'math-foundation-7', 'title' => 'ریاضی پایه هفتم', 'grade' => $grades['7'], 'teacher' => $teachers->get(0), 'price' => 1850000],
                ['slug' => 'science-foundation-8', 'title' => 'علوم پایه هشتم', 'grade' => $grades['8'], 'teacher' => $teachers->get(1), 'price' => 1950000],
                ['slug' => 'gifted-prep-9', 'title' => 'آمادگی تیزهوشان پایه نهم', 'grade' => $grades['9'], 'teacher' => $teachers->get(2), 'price' => 2950000],
            ];

            foreach ($courseDefinitions as $definition) {
                $course = Course::firstOrCreate(
                    ['academy_id' => $academy->id, 'slug' => $definition['slug']],
                    [
                        'created_by' => $owner->id,
                        'title' => $definition['title'],
                        'short_description' => 'دوره کامل و ساختاریافته آکادمی شیخان برای یادگیری عمیق و حل مسئله.',
                        'description' => 'این دوره شامل جلسات آنلاین، منابع آموزشی، تکالیف، آزمون و گزارش پیشرفت است.',
                        'level' => 'متوسط',
                        'status' => 'published',
                        'access_type' => 'paid',
                        'price' => $definition['price'],
                        'duration_minutes' => 1440,
                        'published_at' => now()->subDays(20),
                    ]
                );
                $course->grades()->syncWithoutDetaching([$definition['grade']->id]);
                $course->teachers()->syncWithoutDetaching([$definition['teacher']->id => ['is_primary' => true]]);
                $courses->push($course);

                $section = CourseSection::firstOrCreate(
                    ['course_id' => $course->id, 'sort_order' => 1],
                    ['title' => 'بخش اول: شروع مسیر', 'description' => 'مفاهیم پایه و شروع مسیر آموزشی.']
                );

                $lessonTitles = [
                    'آشنایی با مسیر یادگیری',
                    'مفاهیم کلیدی و نکات پایه',
                    'حل نمونه سوال و تمرین',
                    'جمع‌بندی جلسه و تکلیف',
                ];

                foreach ($lessonTitles as $index => $title) {
                    $lesson = Lesson::firstOrCreate(
                        ['course_section_id' => $section->id, 'slug' => $course->slug . '-lesson-' . ($index + 1)],
                        [
                            'title' => $title,
                            'type' => 'video',
                            'summary' => 'ویدئوی آموزشی ساختاریافته برای دانش‌آموز.',
                            'content' => 'محتوای درس برای استفاده در پنل مدرس و کارتابل دانش‌آموز.',
                            'duration_seconds' => 1200 + ($index * 300),
                            'is_free' => $index === 0,
                            'status' => 'published',
                            'published_at' => now()->subDays(15 - $index),
                            'sort_order' => $index + 1,
                        ]
                    );

                    LessonProgress::firstOrCreate(
                        ['lesson_id' => $lesson->id, 'user_id' => $students->get($index % 2)->id],
                        [
                            'progress_percent' => $index === 0 ? 100 : 55,
                            'seconds_watched' => $index === 0 ? 1200 : 650,
                            'completed_at' => $index === 0 ? now()->subDays(2) : null,
                            'last_watched_at' => now()->subDays($index + 1),
                        ]
                    );
                }
            }

            $classroomDefinitions = [
                ['code' => 'MATH7-01', 'title' => 'کلاس ریاضی هفتم - گروه ۱', 'course' => $courses->get(0), 'grade' => $grades['7'], 'teacher' => $teachers->get(0), 'students' => $students->filter(fn (User $s) => in_array($s->studentProfile?->grade, ['هفتم'], true))],
                ['code' => 'SCI8-01', 'title' => 'کلاس علوم هشتم - گروه ۱', 'course' => $courses->get(1), 'grade' => $grades['8'], 'teacher' => $teachers->get(1), 'students' => $students->filter(fn (User $s) => in_array($s->studentProfile?->grade, ['هشتم'], true))],
                ['code' => 'GIFT9-01', 'title' => 'آمادگی تیزهوشان نهم - گروه ۱', 'course' => $courses->get(2), 'grade' => $grades['9'], 'teacher' => $teachers->get(2), 'students' => $students->filter(fn (User $s) => in_array($s->studentProfile?->grade, ['نهم'], true))],
            ];

            $classrooms = collect();
            foreach ($classroomDefinitions as $definition) {
                $classroom = Classroom::firstOrCreate(
                    ['academy_id' => $academy->id, 'code' => $definition['code']],
                    [
                        'course_id' => $definition['course']->id,
                        'grade_id' => $definition['grade']->id,
                        'academic_year_id' => $year->id,
                        'title' => $definition['title'],
                        'description' => 'کلاس فعال نمونه برای تست پنل مدرس و دانش‌آموز.',
                        'capacity' => 20,
                        'status' => 'active',
                        'starts_at' => now()->subMonth(),
                        'ends_at' => now()->addMonths(8),
                    ]
                );
                $classroom->teachers()->syncWithoutDetaching([$definition['teacher']->id]);

                foreach ($definition['students'] as $student) {
                    $classroom->students()->syncWithoutDetaching([
                        $student->id => [
                            'status' => 'active',
                            'enrolled_at' => now()->subMonth(),
                        ],
                    ]);

                    CourseEnrollment::firstOrCreate(
                        ['course_id' => $definition['course']->id, 'student_id' => $student->id],
                        [
                            'classroom_id' => $classroom->id,
                            'academic_year_id' => $year->id,
                            'registered_by' => $owner->id,
                            'registration_source' => 'admin',
                            'status' => 'active',
                            'paid_amount' => $definition['course']->price,
                            'started_at' => now()->subMonth(),
                        ]
                    );

                    Attendance::firstOrCreate(
                        ['classroom_id' => $classroom->id, 'student_id' => $student->id, 'attendance_date' => now()->subDays(3)->toDateString()],
                        ['marked_by' => $definition['teacher']->id, 'status' => 'present']
                    );
                }

                ClassSchedule::firstOrCreate(
                    ['classroom_id' => $classroom->id, 'weekday' => 2],
                    ['start_time' => '17:00', 'end_time' => '18:30', 'room' => 'کلاس آنلاین', 'meeting_url' => 'https://example.com/sheykhan/' . $definition['code']]
                );

                $classrooms->push($classroom);
            }

            $this->seedLiveClasses($courses, $classrooms, $teachers, $owner);
            $this->seedAssignments($courses, $classrooms, $teachers, $students);
            $this->seedExams($courses, $classrooms, $teachers, $students);
            $this->seedMediaAndResources($academy, $courses, $classrooms, $students, $owner);
            $this->seedShop($academy, $owner, $students);
            $this->seedPublicContent($academy, $owner, $students, $grades, $year);
            $this->seedBlog($owner);
            $this->seedSeoAndSettings($academy);
            $this->seedOnboardingAndAudit($academy, $owner, $grades, $students);
        });
    }

    private function seedReferenceData(): array
    {
        $grades = [];
        foreach ([
            ['code' => '7', 'title' => 'پایه هفتم', 'sort_order' => 7],
            ['code' => '8', 'title' => 'پایه هشتم', 'sort_order' => 8],
            ['code' => '9', 'title' => 'پایه نهم', 'sort_order' => 9],
            ['code' => '10', 'title' => 'پایه دهم', 'sort_order' => 10],
            ['code' => '11', 'title' => 'پایه یازدهم', 'sort_order' => 11],
            ['code' => '12', 'title' => 'پایه دوازدهم', 'sort_order' => 12],
        ] as $data) {
            $grade = AcademicGrade::firstOrCreate(['code' => $data['code']], $data);
            $grades[$data['code']] = $grade;
        }

        AcademicYear::firstOrCreate(
            ['title' => '1405-1406'],
            ['start_date' => '2026-09-23', 'end_date' => '2027-09-22', 'is_current' => true]
        );

        return $grades;
    }

    private function user(string $email, string $name, string $key, string $roleSlug): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'mobile' => '09' . str_pad((string) (1000000000 + crc32($key) % 899999999), 10, '0', STR_PAD_LEFT),
                'status' => 'active',
                'password' => Hash::make('Sheykhan@12345'),
            ]
        );

        $user->roles()->syncWithoutDetaching([
            DB::table('roles')->where('slug', $roleSlug)->value('id'),
        ]);

        return $user;
    }

    private function student(string $email, string $name, string $number, string $gradeLabel, AcademicGrade $grade): User
    {
        $user = $this->user($email, $name, 'student-' . $number, 'student');

        StudentProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'student_number' => $number,
                'birth_date' => '2013-05-10',
                'grade' => $gradeLabel,
                'grade_id' => $grade->id,
                'school_name' => 'مدرسه منتخب شیخان',
                'bio' => 'دانش‌آموز ثبت‌شده در آکادمی شیخان.',
                'registration_source' => 'admin',
                'onboarded_at' => now()->subMonths(2),
                'status' => 'active',
            ]
        );

        return $user->fresh(['studentProfile']);
    }

    private function parent(string $email, string $name, string $relation, User $student): User
    {
        $parent = $this->user($email, $name, 'parent-' . $student->id, 'parent');

        $parent->parentProfile()->updateOrCreate([], [
            'occupation' => 'والد',
            'relation_default' => $relation,
        ]);

        $parent->children()->syncWithoutDetaching([
            $student->id => ['relation' => strtolower($relation)],
        ]);

        return $parent;
    }

    private function seedLiveClasses($courses, $classrooms, $teachers, User $owner): void
    {
        foreach ([
            ['course' => 0, 'classroom' => 0, 'teacher' => 0, 'title' => 'جلسه ۱ ریاضی: حل مسئله', 'daysAgo' => 8, 'status' => 'completed', 'released' => true],
            ['course' => 0, 'classroom' => 0, 'teacher' => 0, 'title' => 'جلسه ۲ ریاضی: الگوها', 'daysAgo' => 2, 'status' => 'completed', 'released' => false],
            ['course' => 1, 'classroom' => 1, 'teacher' => 1, 'title' => 'جلسه ۱ علوم: انرژی', 'daysAgo' => -1, 'status' => 'scheduled', 'released' => false],
            ['course' => 2, 'classroom' => 2, 'teacher' => 2, 'title' => 'جلسه جمع‌بندی تیزهوشان', 'daysAgo' => 5, 'status' => 'completed', 'released' => true],
        ] as $data) {
            $scheduled = $data['daysAgo'] >= 0 ? now()->subDays($data['daysAgo'])->setTime(18, 0) : now()->addDays(abs($data['daysAgo']))->setTime(18, 0);

            $recordingMediaId = null;
            if ($data['released']) {
                $media = $this->media(
                    'live-class-recording-' . $data['course'],
                    $owner->id,
                    'video/mp4',
                    'mp4',
                    'live-classes',
                    'private'
                );
                $recordingMediaId = $media->id;
            }

            LiveClass::firstOrCreate(
                ['course_id' => $courses->get($data['course'])->id, 'title' => $data['title']],
                [
                    'classroom_id' => $classrooms->get($data['classroom'])->id,
                    'teacher_id' => $teachers->get($data['teacher'])->id,
                    'recording_media_id' => $recordingMediaId,
                    'description' => 'جلسه قابل مشاهده در کارتابل دانش‌آموز.',
                    'provider' => 'internal',
                    'meeting_url' => 'https://example.com/live/' . $courses->get($data['course'])->slug,
                    'scheduled_at' => $scheduled,
                    'scheduled_end_at' => $scheduled->copy()->addMinutes(90),
                    'started_at' => $data['status'] === 'completed' ? $scheduled : null,
                    'ended_at' => $data['status'] === 'completed' ? $scheduled->copy()->addMinutes(90) : null,
                    'recording_released_at' => $data['released'] ? $scheduled->copy()->addDay() : null,
                    'recording_visibility' => 'enrolled',
                    'duration_minutes' => 90,
                    'status' => $data['status'],
                ]
            );
        }
    }

    private function seedAssignments($courses, $classrooms, $teachers, $students): void
    {
        foreach ($courses as $index => $course) {
            $assignment = Assignment::firstOrCreate(
                ['course_id' => $course->id, 'title' => 'تکلیف جلسه اول - ' . $course->title],
                [
                    'classroom_id' => $classrooms->get($index)->id,
                    'teacher_id' => $teachers->get($index)->id,
                    'instructions' => 'تمرین‌های جلسه را حل کنید و پاسخ تشریحی را در کارتابل ارسال کنید.',
                    'due_at' => now()->addDays(5),
                    'max_score' => 20,
                    'status' => 'published',
                ]
            );

            $student = $students->filter(fn (User $s) => $s->classroomsAsStudent()->whereKey($classrooms->get($index)->id)->exists())->first();
            if ($student) {
                AssignmentSubmission::firstOrCreate(
                    ['assignment_id' => $assignment->id, 'student_id' => $student->id],
                    [
                        'content' => 'پاسخ تمرین آماده و ارسال شد.',
                        'submitted_at' => now()->subDay(),
                        'score' => 18,
                        'feedback' => 'راه‌حل مناسب است؛ توضیحات سؤال دوم را کامل‌تر کنید.',
                        'graded_at' => now()->subHours(10),
                        'graded_by' => $teachers->get($index)->id,
                    ]
                );
            }
        }
    }

    private function seedExams($courses, $classrooms, $teachers, $students): void
    {
        foreach ($courses as $index => $course) {
            $exam = Exam::firstOrCreate(
                ['course_id' => $course->id, 'title' => 'آزمون میان‌ترم - ' . $course->title],
                [
                    'classroom_id' => $classrooms->get($index)->id,
                    'teacher_id' => $teachers->get($index)->id,
                    'description' => 'آزمون استاندارد برای ارزیابی یادگیری نیم‌دوره.',
                    'duration_minutes' => 45,
                    'starts_at' => now()->subDays(2),
                    'ends_at' => now()->addDays(7),
                    'attempts_allowed' => 2,
                    'status' => 'published',
                ]
            );

            $questions = [];
            foreach ([
                ['text' => 'کدام گزینه صحیح است؟', 'correct' => 'a'],
                ['text' => 'پاسخ درست مسئله کدام است؟', 'correct' => 'b'],
                ['text' => 'بهترین روش حل کدام است؟', 'correct' => 'c'],
            ] as $qIndex => $q) {
                $questions[] = Question::firstOrCreate(
                    ['exam_id' => $exam->id, 'sort_order' => $qIndex + 1],
                    [
                        'type' => 'multiple_choice',
                        'question' => $q['text'],
                        'options' => ['a' => 'گزینه اول', 'b' => 'گزینه دوم', 'c' => 'گزینه سوم', 'd' => 'گزینه چهارم'],
                        'correct_answer' => $q['correct'],
                        'score' => 1,
                        'sort_order' => $qIndex + 1,
                    ]
                );
            }

            $student = $students->filter(fn (User $s) => $s->classroomsAsStudent()->whereKey($classrooms->get($index)->id)->exists())->first();
            if ($student) {
                $attempt = ExamAttempt::firstOrCreate(
                    ['exam_id' => $exam->id, 'student_id' => $student->id, 'attempt_number' => 1],
                    [
                        'started_at' => now()->subDay(),
                        'submitted_at' => now()->subDay()->addMinutes(35),
                        'score' => 16,
                        'status' => 'submitted',
                    ]
                );

                foreach ($questions as $qIndex => $question) {
                    ExamAnswer::firstOrCreate(
                        ['exam_attempt_id' => $attempt->id, 'question_id' => $question->id],
                        [
                            'answer' => $qIndex === 0 ? 'a' : 'b',
                            'is_correct' => $qIndex === 0,
                            'score' => $qIndex === 0 ? 1 : 0,
                        ]
                    );
                }
            }
        }
    }

    private function seedMediaAndResources(Academy $academy, $courses, $classrooms, $students, User $owner): void
    {
        foreach ($courses as $index => $course) {
            $media = $this->media('course-resource-' . ($index + 1), $owner->id, 'application/pdf', 'pdf', 'learning-resources', 'private');

            LearningResource::firstOrCreate(
                ['academy_id' => $academy->id, 'course_id' => $course->id, 'title' => 'جزوه جلسه اول - ' . $course->title],
                [
                    'classroom_id' => $classrooms->get($index)->id,
                    'media_id' => $media->id,
                    'uploaded_by' => $owner->id,
                    'description' => 'جزوه آموزشی اختصاصی دانش‌آموزان ثبت‌نام‌شده.',
                    'resource_type' => 'pdf',
                    'visibility' => 'enrolled_students',
                    'release_at' => now()->subDay(),
                    'downloadable' => true,
                    'status' => 'published',
                    'sort_order' => 1,
                ]
            );
        }
    }

    private function seedShop(Academy $academy, User $owner, $students): void
    {
        $categories = [
            'books' => ProductCategory::firstOrCreate(['slug' => 'books'], ['name' => 'کتابخانه', 'description' => 'تألیفات و ترجمه‌ها', 'sort_order' => 1, 'is_active' => true]),
            'booklets' => ProductCategory::firstOrCreate(['slug' => 'booklets'], ['name' => 'جزوه', 'description' => 'جزوه‌های آموزشی آکادمی', 'sort_order' => 2, 'is_active' => true]),
            'exams' => ProductCategory::firstOrCreate(['slug' => 'exams'], ['name' => 'آزمون', 'description' => 'آزمون‌های استاندارد', 'sort_order' => 3, 'is_active' => true]),
        ];

        $products = [
            ['slug' => 'book-smart-thinking', 'title' => 'کتاب تفکر هوشمند', 'category' => $categories['books'], 'price' => 320000, 'type' => 'book'],
            ['slug' => 'booklet-math-7', 'title' => 'جزوه جامع ریاضی هفتم', 'category' => $categories['booklets'], 'price' => 180000, 'type' => 'booklet'],
            ['slug' => 'exam-gifted-9-1', 'title' => 'آزمون جامع تیزهوشان نهم شماره ۱', 'category' => $categories['exams'], 'price' => 95000, 'type' => 'exam'],
        ];

        foreach ($products as $data) {
            $product = Product::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'academy_id' => $academy->id,
                    'category_id' => $data['category']->id,
                    'created_by' => $owner->id,
                    'title' => $data['title'],
                    'subtitle' => 'محصول آموزشی قابل خرید و مدیریت از پنل.',
                    'description' => 'این محصول برای تست چرخه کامل فروش، رضایتنامه حقوقی و دسترسی بعد از خرید ثبت شده است.',
                    'product_type' => $data['type'],
                    'delivery_type' => 'download',
                    'price' => $data['price'],
                    'sale_price' => null,
                    'currency' => 'IRR',
                    'status' => 'published',
                    'is_featured' => true,
                    'published_at' => now()->subDays(5),
                ]
            );

            $media = $this->media('product-' . $data['slug'], $owner->id, 'application/pdf', 'pdf', 'products', 'private');

            ProductFile::firstOrCreate(
                ['product_id' => $product->id, 'media_id' => $media->id],
                ['version' => '1.0', 'is_primary' => true, 'is_preview' => false, 'requires_watermark' => true]
            );

            $student = $students->first();
            $order = Order::firstOrCreate(
                ['order_number' => 'SHK-' . str_pad((string) $product->id, 6, '0', STR_PAD_LEFT)],
                [
                    'buyer_id' => $student->id,
                    'status' => 'paid',
                    'currency' => 'IRR',
                    'subtotal' => $data['price'],
                    'discount' => 0,
                    'total' => $data['price'],
                    'billing_name' => $student->name,
                    'billing_mobile' => $student->mobile,
                    'legal_consent_completed' => true,
                    'paid_at' => now()->subDays(3),
                ]
            );

            $item = OrderItem::firstOrCreate(
                ['order_id' => $order->id, 'product_id' => $product->id],
                [
                    'beneficiary_id' => $student->id,
                    'product_title_snapshot' => $product->title,
                    'unit_price' => $data['price'],
                    'quantity' => 1,
                    'total_price' => $data['price'],
                ]
            );

            Payment::firstOrCreate(
                ['order_id' => $order->id, 'gateway' => 'seed-bank'],
                [
                    'amount' => $data['price'],
                    'currency' => 'IRR',
                    'status' => 'successful',
                    'authority' => 'SEED-AUTH-' . $product->id,
                    'transaction_id' => 'SEED-TXN-' . $product->id,
                    'tracking_code' => 'SEED-' . $product->id,
                    'gateway_response' => ['seed' => true],
                    'paid_at' => now()->subDays(3),
                ]
            );

            $entitlement = ProductEntitlement::firstOrCreate(
                ['order_item_id' => $item->id],
                [
                    'user_id' => $student->id,
                    'product_id' => $product->id,
                    'status' => 'active',
                    'starts_at' => now()->subDays(3),
                    'expires_at' => null,
                    'granted_at' => now()->subDays(3),
                ]
            );

            $file = $product->files()->first();
            ProductDownload::firstOrCreate(
                ['entitlement_id' => $entitlement->id, 'product_file_id' => $file->id],
                ['ip_address' => '127.0.0.1', 'user_agent' => 'Seeder', 'downloaded_at' => now()->subDay()]
            );

            ProtectedFile::firstOrCreate(
                ['product_file_id' => $file->id, 'entitlement_id' => $entitlement->id],
                [
                    'source_checksum' => $file->media->checksum,
                    'generated_path' => 'protected/seed/' . $product->slug . '.pdf',
                    'watermark_text' => $student->name . ' | ' . $order->order_number,
                    'watermark_type' => 'text',
                    'generated_at' => now()->subDays(3),
                    'status' => 'ready',
                    'download_count' => 1,
                ]
            );
        }
    }

    private function seedPublicContent(Academy $academy, User $owner, $students, $grades, AcademicYear $year): void
    {
        $categorySeeds = [
            ['slug' => 'parents', 'title' => 'سخنی با اولیاء'],
            ['slug' => 'students', 'title' => 'سخنی با دانش‌آموزان'],
            ['slug' => 'gifted', 'title' => 'سلام تیزهوشان'],
            ['slug' => 'foreign-resources', 'title' => 'نکاتی از منابع خارجی'],
            ['slug' => 'question-designer', 'title' => 'اگر من طراح سوال بودم'],
        ];

        foreach ($categorySeeds as $categoryData) {
            $category = AcademyContentCategory::firstOrCreate(
                ['academy_id' => $academy->id, 'slug' => $categoryData['slug']],
                ['title' => $categoryData['title'], 'sort_order' => array_search($categoryData['slug'], array_column($categorySeeds, 'slug'), true) + 1, 'is_active' => true]
            );

            for ($i = 1; $i <= 3; $i++) {
                foreach (['article', 'video'] as $type) {
                    AcademyContent::firstOrCreate(
                        ['academy_id' => $academy->id, 'slug' => $categoryData['slug'] . '-' . $type . '-' . $i],
                        [
                            'category_id' => $category->id,
                            'type' => $type,
                            'title' => $categoryData['title'] . ' - ' . ($type === 'video' ? 'ویدئو' : 'مقاله') . ' ' . $i,
                            'excerpt' => 'محتوای رسمی آکادمی شیخان برای معرفی روش آموزشی و تجربه یادگیری.',
                            'body' => 'این محتوای اولیه توسط Seeder ثبت شده و از پنل قابل ویرایش و انتشار است.',
                            'video_duration_seconds' => $type === 'video' ? 240 : null,
                            'status' => 'published',
                            'is_featured' => $i === 1,
                            'sort_order' => $i,
                            'published_at' => now()->subDays($i),
                            'created_by' => $owner->id,
                        ]
                    );
                }
            }
        }

        $achievementData = [
            [$students->get(0), 'gifted_school', 'علامه حلی ۱'],
            [$students->get(1), 'sample_school', 'مدرسه نمونه دولتی فرزانگان'],
            [$students->get(2), 'gifted_school', 'علامه حلی ۲'],
        ];

        foreach ($achievementData as $index => [$student, $type, $school]) {
            Achievement::firstOrCreate(
                ['academy_id' => $academy->id, 'student_id' => $student->id, 'title' => 'افتخارآفرین شماره ' . ($index + 1)],
                [
                    'display_name' => $student->name,
                    'achievement_type' => $type,
                    'school_name' => $school,
                    'grade_id' => $student->studentProfile?->grade_id,
                    'academic_year_id' => $year->id,
                    'description' => 'نمونه رکورد برای نمایش بخش افتخارآفرینان آکادمی شیخان.',
                    'status' => 'published',
                    'is_featured' => true,
                    'published_at' => now()->subDays($index),
                    'created_by' => $owner->id,
                ]
            );
        }

        foreach ([
            ['display_name' => 'مریم محمدی', 'role' => 'parent', 'text' => 'از روند کلاس‌ها و پیگیری مدرس‌ها رضایت دارم.'],
            ['display_name' => 'آرین محمدی', 'role' => 'student', 'text' => 'جلسات ضبط‌شده کمک می‌کند هر زمان لازم شد دوباره مرور کنم.'],
            ['display_name' => 'حسین حسینی', 'role' => 'parent', 'text' => 'گزارش پیشرفت و آزمون‌ها برای ما خیلی کاربردی است.'],
        ] as $index => $data) {
            Testimonial::firstOrCreate(
                ['academy_id' => $academy->id, 'display_name' => $data['display_name']],
                [
                    'user_id' => $students->get($index % $students->count())->id,
                    'role' => $data['role'],
                    'content_text' => $data['text'],
                    'status' => 'approved',
                    'is_featured' => true,
                    'sort_order' => $index + 1,
                    'published_at' => now()->subDays(2 - $index),
                ]
            );
        }

        $legalDocs = LegalDocument::query()->whereIn('code', ['purchase-terms', 'copyright'])->get();
        foreach ($legalDocs as $document) {
            LegalConsent::firstOrCreate(
                ['user_id' => $students->first()->id, 'document_id' => $document->id],
                [
                    'order_id' => Order::query()->first()?->id,
                    'document_version' => $document->version,
                    'consent_type' => 'accepted',
                    'content_hash' => $document->content_hash,
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Seeder',
                    'accepted_at' => now()->subDays(3),
                ]
            );
        }
    }

    private function seedBlog(User $owner): void
    {
        $category = BlogCategory::firstOrCreate(
            ['slug' => 'learning'],
            ['name' => 'یادگیری', 'description' => 'مقالات آموزشی و مهارت مطالعه']
        );

        $tags = collect([
            BlogTag::firstOrCreate(['slug' => 'study-skills'], ['name' => 'مهارت مطالعه']),
            BlogTag::firstOrCreate(['slug' => 'gifted'], ['name' => 'تیزهوشان']),
            BlogTag::firstOrCreate(['slug' => 'exam-prep'], ['name' => 'آمادگی آزمون']),
        ]);

        $posts = [
            ['slug' => 'effective-study-plan', 'title' => 'چطور برنامه مطالعه مؤثر بسازیم؟'],
            ['slug' => 'gifted-exam-strategy', 'title' => 'استراتژی حل سوالات تیزهوشان'],
            ['slug' => 'parent-learning-support', 'title' => 'نقش والدین در مسیر یادگیری'],
        ];

        foreach ($posts as $postData) {
            $post = BlogPost::firstOrCreate(
                ['slug' => $postData['slug']],
                [
                    'category_id' => $category->id,
                    'author_id' => $owner->id,
                    'title' => $postData['title'],
                    'excerpt' => 'مقاله آموزشی برای توسعه محتوای عمومی آکادمی.',
                    'content' => 'این مقاله اولیه برای تست کامل Blog، SEO و انتشار عمومی ثبت شده است.',
                    'status' => 'published',
                    'published_at' => now()->subDays(4),
                ]
            );
            $post->tags()->sync($tags->pluck('id')->all());
            $post->media()->syncWithoutDetaching([$this->media('blog-' . $post->slug, $owner->id, 'image/svg+xml', 'svg', 'blog', 'public')->id => ['collection' => 'featured', 'sort_order' => 0, 'is_featured' => true]]);
        }
    }

    private function seedSeoAndSettings(Academy $academy): void
    {
        foreach ([
            [$academy, 'آکادمی شیخان | آموزش آنلاین', 'آکادمی شیخان؛ آموزش آنلاین، آزمون و محتوای آموزشی.'],
        ] as [$entity, $title, $description]) {
            SeoMeta::firstOrCreate(
                ['seoable_type' => get_class($entity), 'seoable_id' => $entity->id],
                ['title' => $title, 'description' => $description, 'keywords' => 'آکادمی شیخان, آموزش آنلاین, تیزهوشان', 'robots' => 'index,follow', 'og_title' => $title, 'og_description' => $description]
            );
        }

        $settings = [
            'academy.name' => ['value' => $academy->name, 'type' => 'string', 'group' => 'academy', 'is_public' => true],
            'academy.phone' => ['value' => $academy->phone, 'type' => 'string', 'group' => 'academy', 'is_public' => true],
            'academy.default_currency' => ['value' => 'IRR', 'type' => 'string', 'group' => 'commerce', 'is_public' => false],
        ];
        foreach ($settings as $key => $data) {
            Setting::firstOrCreate(['key' => $key], $data);
        }
    }

    private function seedOnboardingAndAudit(Academy $academy, User $owner, $grades, $students): void
    {
        StudentOnboarding::firstOrCreate(
            ['academy_id' => $academy->id, 'national_id_lookup' => hash_hmac('sha256', '0012345678', config('app.key'))],
            [
                'admin_id' => $owner->id,
                'student_id' => $students->get(4)?->id,
                'entered_name' => $students->get(4)?->name ?? 'متین احمدی',
                'requested_grade_id' => $grades['8']->id,
                'school_name' => 'مدرسه منتخب شیخان',
                'mobile' => $students->get(4)?->mobile,
                'source' => 'legacy',
                'status' => 'activated',
                'notes' => 'رکورد تست فرآیند ورود دانش‌آموز قدیمی.',
                'verified_at' => now()->subDays(10),
                'activated_at' => now()->subDays(9),
            ]
        );

        DB::table('audit_logs')->updateOrInsert(
            ['actor_id' => $owner->id, 'action' => 'seed.platform_initialized', 'subject_type' => Academy::class, 'subject_id' => $academy->id],
            ['old_values' => null, 'new_values' => json_encode(['seed' => 'platform-data'], JSON_UNESCAPED_UNICODE), 'ip_address' => '127.0.0.1', 'user_agent' => 'PlatformDataSeeder', 'created_at' => now(), 'updated_at' => now()]
        );
    }

    private function media(string $key, int $uploadedBy, string $mime, string $extension, string $collection, string $visibility): Media
    {
        $path = 'seed/' . $collection . '/' . $key . '.' . $extension;
        $disk = $visibility === 'public' ? 'public' : 'local';
        $storage = Storage::disk($disk);

        if (!$storage->exists($path)) {
            $contents = match ($extension) {
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 675"><rect width="1200" height="675" fill="#31426b"/><text x="80" y="340" fill="#fff" font-size="56" font-family="Arial">Sheykhan</text></svg>',
                'pdf' => "%PDF-1.4\n% Sheykhan seeded file\n",
                default => 'Sheykhan seeded media placeholder',
            };
            $storage->put($path, $contents);
        }

        return Media::firstOrCreate(
            ['disk' => $disk, 'path' => $path],
            [
                'uploaded_by' => $uploadedBy,
                'original_name' => basename($path),
                'file_name' => basename($path),
                'mime_type' => $mime,
                'extension' => $extension,
                'size' => $storage->size($path),
                'checksum' => hash('sha256', $storage->get($path)),
                'visibility' => $visibility,
                'collection' => $collection,
                'metadata' => ['seeded' => true, 'managed_by_admin' => true],
                'status' => 'active',
            ]
        );
    }
}
