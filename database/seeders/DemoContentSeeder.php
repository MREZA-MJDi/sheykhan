<?php

namespace Database\Seeders;

use App\Models\Academy;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\LiveClass;
use App\Models\ParentProfile;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->seedUsersAndRoles();
        });
    }

    private function seedUsersAndRoles(): void
    {
        $owner = User::updateOrCreate(
            ['email' => 'owner@sheykhan.test'],
            ['name' => 'مدیر شیخان', 'password' => 'password']
        );

        $teachers = collect([
            ['email' => 'teacher1@sheykhan.test', 'name' => 'سارا احمدی'],
            ['email' => 'teacher2@sheykhan.test', 'name' => 'علی رضایی'],
        ])->map(function (array $data): User {
            return User::updateOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => 'password']
            );
        });

        $students = collect([
            ['email' => 'student1@sheykhan.test', 'name' => 'آرین محمدی'],
            ['email' => 'student2@sheykhan.test', 'name' => 'نگار کریمی'],
            ['email' => 'student3@sheykhan.test', 'name' => 'پارسا حسینی'],
        ])->map(function (array $data): User {
            return User::updateOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => 'password']
            );
        });

        $parent = User::updateOrCreate(
            ['email' => 'parent@sheykhan.test'],
            ['name' => 'مریم محمدی', 'password' => 'password']
        );

        $owner->roles()->syncWithoutDetaching([Role::where('slug', 'academy-owner')->value('id')]);
        $teachers->each(fn (User $teacher) => $teacher->roles()->syncWithoutDetaching([
            Role::where('slug', 'teacher')->value('id'),
        ]));
        $students->each(fn (User $student) => $student->roles()->syncWithoutDetaching([
            Role::where('slug', 'student')->value('id'),
        ]));
        $parent->roles()->syncWithoutDetaching([Role::where('slug', 'parent')->value('id')]);

        foreach ($teachers as $index => $teacher) {
            TeacherProfile::updateOrCreate(
                ['user_id' => $teacher->id],
                [
                    'bio' => $index === 0
                        ? 'مدرس برنامه‌نویسی و طراحی وب با تمرکز بر یادگیری پروژه‌محور.'
                        : 'مدرس ریاضی و علوم با تمرکز بر حل مسئله و آموزش مفهومی.',
                    'specialization' => $index === 0 ? 'برنامه‌نویسی و وب' : 'ریاضی و علوم',
                    'education' => $index === 0 ? 'مهندسی کامپیوتر' : 'مهندسی برق',
                    'experience_years' => 6 + $index * 2,
                    'is_verified' => true,
                ]
            );
        }

        foreach ($students as $index => $student) {
            StudentProfile::updateOrCreate(
                ['user_id' => $student->id],
                [
                    'student_code' => 'SH-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'grade' => $index === 0 ? 'هفتم' : ($index === 1 ? 'هشتم' : 'نهم'),
                    'field' => $index === 2 ? 'ریاضی' : 'عمومی',
                ]
            );
        }

        ParentProfile::updateOrCreate(
            ['user_id' => $parent->id],
            ['phone' => '09120000000']
        );

        $academy = Academy::updateOrCreate(
            ['slug' => 'sheykhan-academy'],
            [
                'owner_id' => $owner->id,
                'name' => 'آکادمی شیخان',
                'code' => 'SHK-001',
                'description' => 'مرکز آموزشی نمونه برای تست و توسعه پلتفرم شیخان.',
                'phone' => '02100000000',
                'email' => 'academy@sheykhan.test',
                'address' => 'تهران',
                'city' => 'تهران',
                'province' => 'تهران',
                'website' => 'https://sheykhan.test',
                'status' => 'active',
            ]
        );

        $academy->users()->syncWithoutDetaching([
            $owner->id => ['role' => 'owner', 'status' => 'active', 'joined_at' => now()],
        ]);

        foreach ($teachers as $teacher) {
            $academy->users()->syncWithoutDetaching([
                $teacher->id => ['role' => 'teacher', 'status' => 'active', 'joined_at' => now()],
            ]);
        }

        foreach ($students as $student) {
            $academy->users()->syncWithoutDetaching([
                $student->id => ['role' => 'student', 'status' => 'active', 'joined_at' => now()],
            ]);
        }

        $academy->users()->syncWithoutDetaching([
            $parent->id => ['role' => 'parent', 'status' => 'active', 'joined_at' => now()],
        ]);

        $courseData = [
            [
                'slug' => 'web-programming-foundation',
                'title' => 'مبانی برنامه‌نویسی وب',
                'short_description' => 'مسیر پروژه‌محور برای شروع HTML، CSS، JavaScript و منطق وب.',
                'level' => 'مقدماتی',
                'price' => 890000,
                'duration_minutes' => 480,
                'teacher' => $teachers->get(0),
            ],
            [
                'slug' => 'problem-solving-math',
                'title' => 'ریاضی و حل مسئله',
                'short_description' => 'تقویت تفکر حل مسئله با تمرین‌های مرحله‌ای و چالش‌های هوشمند.',
                'level' => 'متوسط',
                'price' => 690000,
                'duration_minutes' => 360,
                'teacher' => $teachers->get(1),
            ],
            [
                'slug' => 'creative-coding',
                'title' => 'کدنویسی خلاق برای نوجوانان',
                'short_description' => 'ساخت پروژه‌های کوچک و جذاب برای یادگیری عمیق مفاهیم برنامه‌نویسی.',
                'level' => 'متوسط',
                'price' => 590000,
                'duration_minutes' => 300,
                'teacher' => $teachers->get(0),
            ],
        ];

        $courses = collect();

        foreach ($courseData as $data) {
            $course = Course::updateOrCreate(
                ['academy_id' => $academy->id, 'slug' => $data['slug']],
                [
                    'created_by' => $owner->id,
                    'title' => $data['title'],
                    'short_description' => $data['short_description'],
                    'description' => $data['short_description'] . ' این دوره برای نمایش و تست جریان واقعی پلتفرم شیخان آماده شده است.',
                    'level' => $data['level'],
                    'status' => 'published',
                    'price' => $data['price'],
                    'duration_minutes' => $data['duration_minutes'],
                    'published_at' => now()->subDays(2),
                ]
            );

            $course->teachers()->syncWithoutDetaching([
                $data['teacher']->id => ['is_primary' => true],
            ]);

            $section = CourseSection::updateOrCreate(
                ['course_id' => $course->id, 'sort_order' => 1],
                [
                    'title' => 'شروع مسیر',
                    'description' => 'اولین بخش آموزشی دوره.',
                ]
            );

            $lessonTitles = ['آشنایی با مسیر یادگیری', 'مفاهیم پایه', 'اولین تمرین عملی'];

            foreach ($lessonTitles as $lessonIndex => $lessonTitle) {
                Lesson::updateOrCreate(
                    [
                        'course_section_id' => $section->id,
                        'slug' => 'lesson-' . ($lessonIndex + 1),
                    ],
                    [
                        'title' => $lessonTitle,
                        'type' => 'video',
                        'summary' => 'درس نمونه برای تست نمایش محتوای آموزشی.',
                        'content' => 'محتوای نمونه این درس برای توسعه و تست سیستم.',
                        'duration_seconds' => 900 + ($lessonIndex * 300),
                        'is_free' => $lessonIndex === 0,
                        'status' => 'published',
                        'published_at' => now()->subDay(),
                        'sort_order' => $lessonIndex + 1,
                    ]
                );
            }

            foreach ($students->take(2) as $student) {
                CourseEnrollment::updateOrCreate(
                    ['course_id' => $course->id, 'student_id' => $student->id],
                    [
                        'status' => 'active',
                        'paid_amount' => $data['price'],
                        'started_at' => now()->subDays(3),
                    ]
                );
            }

            $courses->push($course);
        }

        $classroom = Classroom::updateOrCreate(
            ['academy_id' => $academy->id, 'code' => 'WEB-101'],
            [
                'course_id' => $courses->first()->id,
                'title' => 'کلاس وب مقدماتی',
                'description' => 'کلاس نمونه برای تست پنل و کلاس آنلاین.',
                'capacity' => 20,
                'status' => 'active',
                'starts_at' => now()->subWeek(),
                'ends_at' => now()->addMonths(2),
            ]
        );

        $classroom->teachers()->syncWithoutDetaching([$teachers->first()->id]);
        $classroom->students()->syncWithoutDetaching(
            $students->take(2)->mapWithKeys(fn (User $student) => [
                $student->id => [
                    'status' => 'active',
                    'enrolled_at' => now()->subDays(5),
                ],
            ])->all()
        );

        LiveClass::updateOrCreate(
            ['course_id' => $courses->first()->id, 'title' => 'جلسه زنده آشنایی با وب'],
            [
                'classroom_id' => $classroom->id,
                'teacher_id' => $teachers->first()->id,
                'description' => 'کلاس زنده نمونه برای تست Home و تقویم کلاس‌ها.',
                'provider' => 'internal',
                'meeting_url' => 'https://example.com/live/sheykhan',
                'scheduled_at' => now()->addDay()->setTime(18, 30),
                'duration_minutes' => 90,
                'status' => 'scheduled',
            ]
        );

        $category = BlogCategory::updateOrCreate(
            ['slug' => 'learning'],
            [
                'name' => 'یادگیری',
                'description' => 'مطالب آموزشی شیخان.',
            ]
        );

        $tag = BlogTag::updateOrCreate(
            ['slug' => 'study-skills'],
            ['name' => 'مهارت مطالعه']
        );

        $post = BlogPost::updateOrCreate(
            ['slug' => 'how-to-build-a-learning-path'],
            [
                'category_id' => $category->id,
                'author_id' => $owner->id,
                'title' => 'چطور یک مسیر یادگیری مؤثر بسازیم؟',
                'excerpt' => 'چند اصل ساده برای ساختن یک برنامه یادگیری منظم و قابل پیگیری.',
                'content' => 'این مقاله نمونه برای تست بخش مقالات و اتصال واقعی Blog به Home ساخته شده است.',
                'status' => 'published',
                'published_at' => now()->subHours(8),
            ]
        );

        $post->tags()->syncWithoutDetaching([$tag->id]);

        $parent->children()->syncWithoutDetaching([
            $students->first()->id => ['relation' => 'mother'],
        ]);
    }
}
