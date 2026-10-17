<?php

namespace Database\Seeders;

use App\Models\Academy;
use App\Models\AcademicGrade;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseSection;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\LearningResource;
use App\Models\LiveClass;
use App\Models\Question;
use App\Models\User;
use Database\Seeders\Support\SeedMedia;
use Illuminate\Database\Seeder;

class LearningSeeder extends Seeder
{
    public function run(): void
    {
        $academy=Academy::where('slug','sheykhan-academy')->firstOrFail();
        $owner=User::where('email','owner@sheykhan.test')->firstOrFail();
        $teacherEmails = ['teacher.math@sheykhan.test', 'teacher.science@sheykhan.test', 'teacher.gifted@sheykhan.test'];
        $studentEmails = ['student.armin@sheykhan.test', 'student.nika@sheykhan.test', 'student.parsa@sheykhan.test', 'student.ava@sheykhan.test', 'student.matin@sheykhan.test', 'student.tara@sheykhan.test'];
        // Demo users must be distinct. Falling back to one account silently joins
        // unrelated courses, classrooms and student rosters, hiding authorization bugs.
        $teachers = collect($teacherEmails)->mapWithKeys(fn (string $email) => [
            $email => User::where('email', $email)->firstOrFail(),
        ]);
        $students = collect($studentEmails)->mapWithKeys(fn (string $email) => [
            $email => User::where('email', $email)->firstOrFail(),
        ]);
        $grades=AcademicGrade::get()->keyBy('code');
        $year=AcademicYear::where('title','1405-1406')->firstOrFail();

        $definitions=[
            ['slug'=>'math-foundation-7','title'=>'ریاضی پایه هفتم','grade'=>'7','teacher'=>'teacher.math@sheykhan.test','price'=>1850000],
            ['slug'=>'science-foundation-8','title'=>'علوم پایه هشتم','grade'=>'8','teacher'=>'teacher.science@sheykhan.test','price'=>1950000],
            ['slug'=>'gifted-prep-9','title'=>'آمادگی تیزهوشان پایه نهم','grade'=>'9','teacher'=>'teacher.gifted@sheykhan.test','price'=>2950000],
        ];
        $classroomStudentEmails = [
            'math-foundation-7' => ['student.armin@sheykhan.test', 'student.ava@sheykhan.test'],
            'science-foundation-8' => ['student.nika@sheykhan.test', 'student.matin@sheykhan.test'],
            'gifted-prep-9' => ['student.parsa@sheykhan.test', 'student.tara@sheykhan.test'],
        ];
        $courses=collect();
        foreach($definitions as $d){
            $course=Course::firstOrCreate(['academy_id'=>$academy->id,'slug'=>$d['slug']],[
                'created_by'=>$owner->id,'title'=>$d['title'],'short_description'=>'مسیر منظم آموزش مفاهیم پایه، تمرین و سنجش پیشرفت.','description'=>'دوره‌ای ساختاریافته با درس‌های ویدئویی، منابع آموزشی، تکالیف و آزمون‌های مرحله‌ای برای پیگیری واقعی یادگیری.',
                'level'=>'متوسط','status'=>'published','access_type'=>'paid','price'=>$d['price'],'duration_minutes'=>1440,'published_at'=>now()->subDays(20)
            ]);
            $course->grades()->syncWithoutDetaching([$grades[$d['grade']]->id]);
            // Each demo course has one intentionally assigned primary teacher.
            $course->teachers()->sync([$teachers[$d['teacher']]->id => ['is_primary' => true]]);
            $section=CourseSection::firstOrCreate(['course_id'=>$course->id,'sort_order'=>1],['title'=>'بخش اول: شروع مسیر','description'=>'مفاهیم پایه و شروع مسیر آموزشی.']);
            $lessonDefinitions = match ($d['slug']) {
                'math-foundation-7' => [
                    ['title' => 'نسبت و تناسب در زندگی روزمره', 'summary' => 'نسبت دو مقدار را بخوان و از تناسب برای حل مسئله استفاده کن.', 'content' => "هدف درس: تشخیص نسبت و ساختن تناسب.\n\nمثال: برای تهیه شربت، به ازای هر ۲ پیمانه شربت، ۵ پیمانه آب می‌ریزیم. نسبت شربت به آب ۲ به ۵ است. اگر ۶ پیمانه شربت داشته باشیم، برای حفظ همان مزه چند پیمانه آب لازم است؟\n\nراه‌حل: چون ۶ سه برابر ۲ است، مقدار آب هم سه برابر می‌شود: ۵ × ۳ = ۱۵ پیمانه.\n\nتمرین: اگر ۴ دفتر ۱۲۰ هزار تومان باشد، قیمت ۷ دفتر با همان قیمت واحد چقدر است؟ راه‌حل را مرحله‌به‌مرحله بنویس.", 'free' => true],
                    ['title' => 'عبارت‌های جبری و جای‌گذاری', 'summary' => 'حروف در عبارت جبری چه معنایی دارند؟', 'content' => "هدف درس: مقداردهی به متغیر و محاسبه عبارت جبری.\n\nدر عبارت ۳x + ۴، حرف x یک مقدار نامعلوم یا متغیر است. اگر x = ۵ باشد، ابتدا ضرب را انجام می‌دهیم: ۳ × ۵ + ۴ = ۱۹.\n\nیادت باشد ترتیب عملیات را رعایت کنی؛ ابتدا پرانتز، سپس ضرب و تقسیم و بعد جمع و تفریق.\n\nتمرین: برای x = ۶ مقدار ۲x + ۷ را حساب کن و توضیح بده کدام مرحله را اول انجام دادی.", 'free' => false],
                    ['title' => 'حل مسئله با معادله یک‌مرحله‌ای', 'summary' => 'متن مسئله را به یک تساوی قابل حل تبدیل کن.', 'content' => "هدف درس: تبدیل جمله به معادله و بررسی جواب.\n\nمثال: عددی را با ۸ جمع کردیم و حاصل ۲۱ شد. عدد را x می‌نامیم: x + ۸ = ۲۱. با کم‌کردن ۸ از دو طرف، x = ۱۳ به دست می‌آید.\n\nبرای بررسی، ۱۳ + ۸ = ۲۱ است؛ پس پاسخ درست است.\n\nتمرین: اگر سه برابر عددی برابر ۲۷ باشد، معادله را بنویس و عدد را پیدا کن.", 'free' => false],
                    ['title' => 'مرور فصل و خودسنجی', 'summary' => 'سه مهارت فصل را قبل از آزمون بررسی کن.', 'content' => "چک‌لیست مرور: ۱) نسبت هم‌ارز را می‌سازی؛ ۲) مقدار متغیر را در عبارت جای‌گذاری می‌کنی؛ ۳) معادله یک‌مرحله‌ای را حل و با جای‌گذاری بررسی می‌کنی.\n\nخودسنجی: بدون نگاه به مثال‌ها، مسئله قیمت ۵ بلیت را از روی قیمت ۲ بلیت حل کن. اگر هنوز در یک مرحله مطمئن نیستی، به درس مربوط برگرد و راه‌حل خودت را با مثال مقایسه کن.", 'free' => false],
                ],
                'science-foundation-8' => [
                    ['title' => 'انرژی و تبدیل‌های آن', 'summary' => 'انرژی از بین نمی‌رود؛ شکل آن در فرایندها تغییر می‌کند.', 'content' => "هدف درس: تشخیص شکل‌های انرژی در یک موقعیت روزمره.\n\nمثال: در چراغ‌قوه، انرژی شیمیایی باتری به انرژی الکتریکی و سپس به انرژی نورانی و گرمایی تبدیل می‌شود. در یک توپ در حال حرکت، انرژی جنبشی داریم؛ هرچه سرعت بیشتر باشد، انرژی جنبشی بیشتر است.\n\nتمرین: مسیر تبدیل انرژی را در یک پنکه و یک اجاق برقی بنویس. مشخص کن در هرکدام چه مقدار از انرژی به شکل گرما ظاهر می‌شود.", 'free' => true],
                    ['title' => 'نیرو، حرکت و اصطکاک', 'summary' => 'اثر نیرو را از تغییر سرعت یا جهت حرکت می‌شناسیم.', 'content' => "هدف درس: توضیح نقش نیرو و اصطکاک.\n\nنیرو می‌تواند سرعت یا جهت حرکت جسم را تغییر دهد یا شکل آن را عوض کند. اصطکاک میان دو سطح معمولاً در برابر لغزش مقاومت می‌کند؛ همین نیرو باعث می‌شود کف کفش به زمین بچسبد، اما مقداری انرژی نیز به گرما تبدیل شود.\n\nتمرین: چرا روی سطح یخی توقف دوچرخه سخت‌تر است؟ پاسخ را با مفهوم اصطکاک توضیح بده.", 'free' => false],
                    ['title' => 'فشار در مایعات', 'summary' => 'فشار مایع با عمق افزایش پیدا می‌کند.', 'content' => "هدف درس: درک رابطه عمق و فشار مایع.\n\nدر یک مایع ساکن، هرچه پایین‌تر برویم، ستون مایع بالای سرمان بلندتر است و فشار بیشتر می‌شود. به همین دلیل سوراخ پایین بطری معمولاً آب را با شدت بیشتری بیرون می‌فرستد.\n\nتمرین: روی یک بطری آب سه سوراخ در ارتفاع‌های متفاوت تصور کن. پیش‌بینی کن کدام جریان دورتر می‌رود و دلیل خود را بنویس.", 'free' => false],
                    ['title' => 'مرور مفاهیم و پرسش پژوهشی', 'summary' => 'دانسته‌ها را با مشاهده و دلیل به هم وصل کن.', 'content' => "برای جمع‌بندی، برای هر پدیده یک مشاهده و یک توضیح علمی بنویس: تبدیل انرژی در پنکه، نقش اصطکاک در توقف دوچرخه و افزایش فشار با عمق.\n\nپرسش پژوهشی: کدام متغیرها روی فاصله حرکت یک اسباب‌بازی پس از رهاشدن اثر می‌گذارند؟ فرضیه بنویس و بگو برای منصفانه بودن آزمایش چه چیزهایی باید ثابت بماند.", 'free' => false],
                ],
                default => [
                    ['title' => 'الگویابی و تشخیص قاعده', 'summary' => 'از چند نمونه، قاعده تولید جمله بعدی را استخراج کن.', 'content' => "هدف درس: کشف قاعده به جای حدس‌زدن پاسخ.\n\nمثال: دنباله ۲، ۶، ۱۲، ۲۰، ۳۰ را بررسی کن. اختلاف‌ها ۴، ۶، ۸ و ۱۰ هستند؛ اختلاف‌ها خودشان هر بار ۲ واحد افزایش دارند. جمله بعدی ۴۲ است، چون اختلاف بعدی ۱۲ خواهد بود.\n\nتمرین: دنباله ۱، ۴، ۹، ۱۶، ۲۵ را بررسی کن. قاعده را توضیح بده و دو جمله بعدی را بنویس.", 'free' => true],
                    ['title' => 'استدلال منطقی و حذف حالت‌ها', 'summary' => 'با شروط مسئله، حالت‌های ناممکن را حذف کن.', 'content' => "در مسائل منطقی، ابتدا داده‌ها را جدا کن، سپس شرط‌های قطعی را به ترتیب اعمال کن. حدس اولیه را تا وقتی با همه شرط‌ها سازگار نشده، پاسخ نهایی تلقی نکن.\n\nتمرین: سه دانش‌آموز در مسابقه رتبه‌های اول تا سوم گرفته‌اند. علی اول نشده، مینا دوم نشده و رضا سوم نشده است. همه حالت‌های ممکن را فهرست کن و با شرط‌های مسئله سازگارها را نگه دار.", 'free' => false],
                    ['title' => 'شمارش نظام‌مند حالت‌ها', 'summary' => 'با جدول یا نمودار درختی، هیچ حالت را جا نینداز.', 'content' => "اگر سه پیراهن و دو شلوار متفاوت داشته باشیم، هر پیراهن با هر شلوار یک ترکیب می‌سازد؛ بنابراین ۳ × ۲ = ۶ ترکیب داریم. فهرست‌کردن منظم حالت‌ها از دوباره‌شماری و جاافتادن پاسخ جلوگیری می‌کند.\n\nتمرین: با چهار کارت رنگی و سه کارت عددی، اگر یک کارت از هر گروه انتخاب شود، چند جفت متفاوت ساخته می‌شود؟ راه‌حل را با جدول یا نمودار درختی نشان بده.", 'free' => false],
                    ['title' => 'مرور راهبردهای حل مسئله', 'summary' => 'قاعده، شرط و شمارش را در یک مسئله ترکیب کن.', 'content' => "پیش از آزمون، این راهبردها را تمرین کن: ۱) داده‌ها و شرط‌ها را جدا بنویس؛ ۲) نمونه‌های کوچک بساز؛ ۳) جدول یا نمودار درختی رسم کن؛ ۴) پاسخ را با تمام شرط‌ها بررسی کن.\n\nخودسنجی: یک مسئله منطقی بساز که دست‌کم سه شرط داشته باشد، آن را با دوستت عوض کن و بررسی کن آیا برای آن پاسخ یکتا وجود دارد.", 'free' => false],
                ],
            };

            foreach ($lessonDefinitions as $i => $definition) {
                $lesson = Lesson::query()->updateOrCreate(
                    ['course_section_id' => $section->id, 'slug' => $d['slug'].'-lesson-'.($i+1)],
                    [
                        'title' => $definition['title'],
                        // The seed set contains real reading content, not fake MP4 placeholders.
                        'type' => 'text',
                        'summary' => $definition['summary'],
                        'content' => $definition['content'],
                        'duration_seconds' => 0,
                        'is_free' => $definition['free'],
                        'status' => 'published',
                        'published_at' => now()->subDays(15 - $i),
                        'sort_order' => $i + 1,
                    ],
                );

                // Seed progress only for learners actually enrolled in this course's classroom.
                $courseStudentEmails = $classroomStudentEmails[$d['slug']] ?? [];
                foreach ($courseStudentEmails as $studentIndex => $email) {
                    $student = $students[$email];
                    $completed = $i === 0 || (($i + $studentIndex) % 3 === 0);

                    LessonProgress::updateOrCreate(
                        ['lesson_id' => $lesson->id, 'user_id' => $student->id],
                        [
                            'progress_percent' => $completed ? 100 : (($i + $studentIndex) % 2 === 0 ? 55 : 25),
                            'seconds_watched' => 0,
                            'completed_at' => $completed ? now()->subDays($i + $studentIndex + 1) : null,
                            'last_watched_at' => now()->subDays($i + $studentIndex + 1),
                        ],
                    );
                }
            }
            $courses->push($course->fresh(['sections.lessons']));
        }

        $classroomDefs=[
            ['code'=>'MATH7-01','title'=>'کلاس ریاضی هفتم - گروه ۱','course'=>0,'grade'=>'7','teacher'=>'teacher.math@sheykhan.test','students'=>['student.armin@sheykhan.test','student.ava@sheykhan.test']],
            ['code'=>'SCI8-01','title'=>'کلاس علوم هشتم - گروه ۱','course'=>1,'grade'=>'8','teacher'=>'teacher.science@sheykhan.test','students'=>['student.nika@sheykhan.test','student.matin@sheykhan.test']],
            ['code'=>'GIFT9-01','title'=>'آمادگی تیزهوشان نهم - گروه ۱','course'=>2,'grade'=>'9','teacher'=>'teacher.gifted@sheykhan.test','students'=>['student.parsa@sheykhan.test','student.tara@sheykhan.test']],
        ];
        $classrooms=collect();
        foreach($classroomDefs as $d){
            $c=Classroom::firstOrCreate(['academy_id'=>$academy->id,'code'=>$d['code']],[
                'course_id'=>$courses->get($d['course'])->id,'grade_id'=>$grades[$d['grade']]->id,'academic_year_id'=>$year->id,
                'title'=>$d['title'],'description'=>'کلاس فعال آموزشی برای تمرین، تعامل با مدرس و پیگیری منظم وضعیت دانش‌آموزان.','capacity'=>20,'status'=>'active','starts_at'=>now()->subMonth(),'ends_at'=>now()->addMonths(8)
            ]);
            // Re-seeding repairs accidental cross-course teacher pivots.
            $c->teachers()->sync([$teachers[$d['teacher']]->id]);
            $expectedStudentIds = [];
            foreach($d['students'] as $email){
                $s = $students[$email];
                $expectedStudentIds[] = (int) $s->id;

                CourseEnrollment::updateOrCreate(
                    [
                        'course_id' => $courses->get($d['course'])->id,
                        'student_id' => $s->id,
                        'academic_year_id' => $year->id,
                    ],
                    [
                        'classroom_id' => $c->id,
                        'registered_by' => $owner->id,
                        'registration_source' => 'admin',
                        'status' => 'active',
                        'payment_status' => 'paid',
                        'price_amount' => $courses->get($d['course'])->price,
                        'paid_amount' => $courses->get($d['course'])->price,
                        'started_at' => now()->subMonth(),
                        'completed_at' => null,
                    ]
                );

                Attendance::updateOrCreate(
                    [
                        'classroom_id' => $c->id,
                        'student_id' => $s->id,
                        'attendance_date' => now()->subDays(3)->toDateString(),
                    ],
                    [
                        'marked_by' => $teachers[$d['teacher']]->id,
                        'status' => 'present',
                        'scheduled_minutes' => 90,
                        'attended_minutes' => 90,
                    ]
                );
            }

            // The Classroom roster is the explicit cohort membership, not every
            // student who has ever purchased the Course.
            $c->students()->sync(collect($expectedStudentIds)->mapWithKeys(
                fn (int $studentId) => [$studentId => [
                    'status' => 'active',
                    'enrolled_at' => now()->subMonth(),
                    'completed_at' => null,
                ]]
            )->all());

            // Preserve historic demo enrollment rows, but cancel a row that was
            // created only because the old fallback reused the same student in
            // a different sample Course. Never delete enrollment history.
            CourseEnrollment::query()
                ->where('course_id', $courses->get($d['course'])->id)
                ->whereIn('student_id', $students->pluck('id'))
                ->whereNotIn('student_id', $expectedStudentIds)
                ->where('status', 'active')
                ->update([
                    'status' => 'cancelled',
                    'classroom_id' => null,
                    'completed_at' => now(),
                ]);
            ClassSchedule::firstOrCreate(['classroom_id'=>$c->id,'weekday'=>2],['start_time'=>'17:00','end_time'=>'18:30','room'=>'کلاس آنلاین','meeting_url'=>'https://meet.jit.si/Sheykhan-'.$d['code']]);
            // A recurring class twice a week is two schedule rows for the same
            // Classroom; keep the room link stable across those weekly slots.
            ClassSchedule::firstOrCreate(['classroom_id'=>$c->id,'weekday'=>4],['start_time'=>'17:00','end_time'=>'18:30','room'=>'کلاس آنلاین','meeting_url'=>'https://meet.jit.si/Sheykhan-'.$d['code']]);
            $classrooms->push($c);
        }

        foreach([
            ['course'=>0,'classroom'=>0,'teacher'=>'teacher.math@sheykhan.test','title'=>'جلسه ۱ ریاضی: حل مسئله','days'=>8,'status'=>'completed','released'=>false],
            ['course'=>0,'classroom'=>0,'teacher'=>'teacher.math@sheykhan.test','title'=>'جلسه ۲ ریاضی: الگوها','days'=>2,'status'=>'completed','released'=>false],
            ['course'=>1,'classroom'=>1,'teacher'=>'teacher.science@sheykhan.test','title'=>'جلسه ۱ علوم: انرژی','days'=>-1,'status'=>'scheduled','released'=>false],
            ['course'=>2,'classroom'=>2,'teacher'=>'teacher.gifted@sheykhan.test','title'=>'جلسه جمع‌بندی تیزهوشان','days'=>5,'status'=>'completed','released'=>false],
        ] as $d){
            $scheduled=$d['days']>=0?now()->subDays($d['days'])->setTime(18,0):now()->addDays(abs($d['days']))->setTime(18,0);
            $media=$d['released']?SeedMedia::make('live-'.$d['course'],$owner->id,'video/mp4','mp4','live-classes','private'):null;
            LiveClass::firstOrCreate(['course_id'=>$courses->get($d['course'])->id,'title'=>$d['title']],[
                'classroom_id'=>$classrooms->get($d['classroom'])->id,'teacher_id'=>$teachers[$d['teacher']]->id,'recording_media_id'=>$media?->id,
                'description'=>'جلسه آنلاین برنامه‌ریزی‌شده با امکان مشاهده زمان، وضعیت و در صورت انتشار، دسترسی به ضبط جلسه.','provider'=>'Jitsi','meeting_url'=>'https://meet.jit.si/Sheykhan-'.$classrooms->get($d['classroom'])->code,
                'scheduled_at'=>$scheduled,'scheduled_end_at'=>$scheduled->copy()->addMinutes(90),'started_at'=>$d['status']==='completed'?$scheduled:null,
                'ended_at'=>$d['status']==='completed'?$scheduled->copy()->addMinutes(90):null,'recording_released_at'=>$d['released']?$scheduled->copy()->addDay():null,
                'recording_visibility'=>'enrolled','duration_minutes'=>90,'status'=>$d['status']
            ]);
        }

        foreach($courses as $i=>$course){
            // Sample assessment data must follow the same teacher and student
            // cohort as its course/classroom; never rotate unrelated learners.
            $classroomDefinition = $classroomDefs[$i] ?? null;
            if (! $classroomDefinition) {
                continue;
            }

            $teacher = $teachers[$classroomDefinition['teacher']];
            $classroom = $classrooms->get((int) $classroomDefinition['course']);
            $student = $students[$classroomDefinition['students'][0]];

            $assignment=Assignment::updateOrCreate(['course_id'=>$course->id,'title'=>'تکلیف هفته '.($i+1)],[
                'classroom_id'=>$classroom->id,'teacher_id'=>$teacher->id,'instructions'=>'تمرین‌های مشخص‌شده را حل کن و راه‌حل مرحله‌به‌مرحله‌ات را در پاسخ ثبت کن.','due_at'=>now()->addDays(7),'max_score'=>100,'status'=>'published'
            ]);
            AssignmentSubmission::updateOrCreate(['assignment_id'=>$assignment->id,'student_id'=>$student->id],[
                'content'=>'پاسخ ثبت‌شده دانش‌آموز برای این تکلیف.','submitted_at'=>now()->subDay(),'score'=>92,'feedback'=>'پاسخ خوب است.','graded_at'=>now()->subHours(12),'graded_by'=>$teacher->id
            ]);
            $exam=Exam::updateOrCreate(['course_id'=>$course->id,'title'=>'آزمون دوره '.($i+1)],[
                'classroom_id'=>$classroom->id,'teacher_id'=>$teacher->id,'description'=>'آزمون مرحله‌ای برای سنجش میزان یادگیری و مشخص‌کردن مباحثی که نیاز به مرور بیشتری دارند.','duration_minutes'=>45,
                'starts_at'=>now()->subDay(),'ends_at'=>now()->addDay(),'attempts_allowed'=>2,'status'=>'published'
            ]);
            $questions=collect();
            foreach([['2+2 چند می‌شود؟',['3','4','5','6'],'4'],['گزینه درست را انتخاب کنید.',['A','B','C','D'],'B']] as $qi=>$q){
                $questions->push(Question::updateOrCreate(['exam_id'=>$exam->id,'sort_order'=>$qi+1],[
                    'type'=>'single','question'=>$q[0],'options'=>$q[1],'correct_answer'=>$q[2],'score'=>1,'sort_order'=>$qi+1
                ]));
            }
            $attempt=ExamAttempt::updateOrCreate(['exam_id'=>$exam->id,'student_id'=>$student->id,'attempt_number'=>1],[
                'started_at'=>now()->subHours(3),'submitted_at'=>now()->subHours(2),'score'=>2,'status'=>'submitted'
            ]);
            foreach($questions as $q){ExamAnswer::updateOrCreate(['exam_attempt_id'=>$attempt->id,'question_id'=>$q->id],['answer'=>$q->correct_answer,'is_correct'=>true,'score'=>$q->score]);}
        }

        $media=SeedMedia::make('math-handout',$owner->id,'application/pdf','pdf','learning-resources','private');
        LearningResource::firstOrCreate(
            ['academy_id'=>$academy->id,'course_id'=>$courses->first()->id,'title'=>'جزوه ریاضی'],
            ['classroom_id'=>$classrooms->first()->id,'lesson_id'=>$courses->first()->sections->first()->lessons->first()->id,'media_id'=>$media->id,'uploaded_by'=>$owner->id,
             'description'=>'منبع آموزشی منتشرشده برای مرور مباحث و تمرین بیشتر.','resource_type'=>'pdf','visibility'=>'enrolled_students','release_at'=>now()->subDay(),'downloadable'=>false,'status'=>'published','sort_order'=>1]
        );
    }
}
