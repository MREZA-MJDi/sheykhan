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
        $teachers=User::whereIn('email',['teacher.math@sheykhan.test','teacher.science@sheykhan.test','teacher.gifted@sheykhan.test'])->get()->keyBy('email');
        $students=User::whereIn('email',['student.armin@sheykhan.test','student.nika@sheykhan.test','student.parsa@sheykhan.test','student.ava@sheykhan.test','student.matin@sheykhan.test','student.tara@sheykhan.test'])->get()->keyBy('email');
        $grades=AcademicGrade::get()->keyBy('code');
        $year=AcademicYear::where('title','1405-1406')->firstOrFail();

        $definitions=[
            ['slug'=>'math-foundation-7','title'=>'ریاضی پایه هفتم','grade'=>'7','teacher'=>'teacher.math@sheykhan.test','price'=>1850000],
            ['slug'=>'science-foundation-8','title'=>'علوم پایه هشتم','grade'=>'8','teacher'=>'teacher.science@sheykhan.test','price'=>1950000],
            ['slug'=>'gifted-prep-9','title'=>'آمادگی تیزهوشان پایه نهم','grade'=>'9','teacher'=>'teacher.gifted@sheykhan.test','price'=>2950000],
        ];
        $courses=collect();
        foreach($definitions as $d){
            $course=Course::firstOrCreate(['academy_id'=>$academy->id,'slug'=>$d['slug']],[
                'created_by'=>$owner->id,'title'=>$d['title'],'short_description'=>'دوره ساختاریافته آکادمی شیخان.','description'=>'شامل جلسات، منابع، تکالیف، آزمون و گزارش پیشرفت.',
                'level'=>'متوسط','status'=>'published','access_type'=>'paid','price'=>$d['price'],'duration_minutes'=>1440,'published_at'=>now()->subDays(20)
            ]);
            $course->grades()->syncWithoutDetaching([$grades[$d['grade']]->id]);
            $course->teachers()->syncWithoutDetaching([$teachers[$d['teacher']]->id=>['is_primary'=>true]]);
            $section=CourseSection::firstOrCreate(['course_id'=>$course->id,'sort_order'=>1],['title'=>'بخش اول: شروع مسیر','description'=>'مفاهیم پایه و شروع مسیر آموزشی.']);
            foreach(['آشنایی با مسیر یادگیری','مفاهیم کلیدی','حل نمونه سؤال','جمع‌بندی و تکلیف'] as $i=>$title){
                $lesson=Lesson::firstOrCreate(['course_section_id'=>$section->id,'slug'=>$d['slug'].'-lesson-'.($i+1)],[
                    'title'=>$title,'type'=>'video','summary'=>'ویدئوی آموزشی برای تست پنل دانش‌آموز.','content'=>'محتوای درس نمونه قابل ویرایش از پنل.',
                    'duration_seconds'=>1200+($i*300),'is_free'=>$i===0,'status'=>'published','published_at'=>now()->subDays(15-$i),'sort_order'=>$i+1
                ]);
                $student=$students->values()->get($i%$students->count());
                LessonProgress::firstOrCreate(['lesson_id'=>$lesson->id,'user_id'=>$student->id],[
                    'progress_percent'=>$i===0?100:55,'seconds_watched'=>$i===0?$lesson->duration_seconds:650,
                    'completed_at'=>$i===0?now()->subDays(2):null,'last_watched_at'=>now()->subDays($i+1)
                ]);
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
                'title'=>$d['title'],'description'=>'کلاس فعال نمونه برای تست پنل مدرس و دانش‌آموز.','capacity'=>20,'status'=>'active','starts_at'=>now()->subMonth(),'ends_at'=>now()->addMonths(8)
            ]);
            $c->teachers()->syncWithoutDetaching([$teachers[$d['teacher']]->id]);
            foreach($d['students'] as $email){
                $s=$students[$email];
                $c->students()->syncWithoutDetaching([$s->id=>['status'=>'active','enrolled_at'=>now()->subMonth()]]);
                CourseEnrollment::firstOrCreate(['course_id'=>$courses->get($d['course'])->id,'student_id'=>$s->id,'academic_year_id'=>$year->id],[
                    'classroom_id'=>$c->id,'registered_by'=>$owner->id,'registration_source'=>'admin','status'=>'active','paid_amount'=>$courses->get($d['course'])->price,'started_at'=>now()->subMonth()
                ]);
                Attendance::firstOrCreate(['classroom_id'=>$c->id,'student_id'=>$s->id,'attendance_date'=>now()->subDays(3)->toDateString()],[
                    'marked_by'=>$teachers[$d['teacher']]->id,'status'=>'present'
                ]);
            }
            ClassSchedule::firstOrCreate(['classroom_id'=>$c->id,'weekday'=>2],['start_time'=>'17:00','end_time'=>'18:30','room'=>'کلاس آنلاین','meeting_url'=>'https://example.com/sheykhan/'.$d['code']]);
            $classrooms->push($c);
        }

        foreach([
            ['course'=>0,'classroom'=>0,'teacher'=>'teacher.math@sheykhan.test','title'=>'جلسه ۱ ریاضی: حل مسئله','days'=>8,'status'=>'completed','released'=>true],
            ['course'=>0,'classroom'=>0,'teacher'=>'teacher.math@sheykhan.test','title'=>'جلسه ۲ ریاضی: الگوها','days'=>2,'status'=>'completed','released'=>false],
            ['course'=>1,'classroom'=>1,'teacher'=>'teacher.science@sheykhan.test','title'=>'جلسه ۱ علوم: انرژی','days'=>-1,'status'=>'scheduled','released'=>false],
            ['course'=>2,'classroom'=>2,'teacher'=>'teacher.gifted@sheykhan.test','title'=>'جلسه جمع‌بندی تیزهوشان','days'=>5,'status'=>'completed','released'=>true],
        ] as $d){
            $scheduled=$d['days']>=0?now()->subDays($d['days'])->setTime(18,0):now()->addDays(abs($d['days']))->setTime(18,0);
            $media=$d['released']?SeedMedia::make('live-'.$d['course'],$owner->id,'video/mp4','mp4','live-classes','private'):null;
            LiveClass::firstOrCreate(['course_id'=>$courses->get($d['course'])->id,'title'=>$d['title']],[
                'classroom_id'=>$classrooms->get($d['classroom'])->id,'teacher_id'=>$teachers[$d['teacher']]->id,'recording_media_id'=>$media?->id,
                'description'=>'جلسه قابل مشاهده در کارتابل دانش‌آموز.','provider'=>'internal','meeting_url'=>'https://example.com/live/'.$courses->get($d['course'])->slug,
                'scheduled_at'=>$scheduled,'scheduled_end_at'=>$scheduled->copy()->addMinutes(90),'started_at'=>$d['status']==='completed'?$scheduled:null,
                'ended_at'=>$d['status']==='completed'?$scheduled->copy()->addMinutes(90):null,'recording_released_at'=>$d['released']?$scheduled->copy()->addDay():null,
                'recording_visibility'=>'enrolled','duration_minutes'=>90,'status'=>$d['status']
            ]);
        }

        foreach($courses as $i=>$course){
            $teacher=$teachers->values()->get($i%$teachers->count()); $classroom=$classrooms->get($i); $student=$students->values()->get($i%$students->count());
            $assignment=Assignment::firstOrCreate(['course_id'=>$course->id,'title'=>'تکلیف هفته '.($i+1)],[
                'classroom_id'=>$classroom->id,'teacher_id'=>$teacher->id,'instructions'=>'حل تمرین‌های مشخص‌شده و ارسال راه‌حل.','due_at'=>now()->addDays(7),'max_score'=>100,'status'=>'published'
            ]);
            AssignmentSubmission::firstOrCreate(['assignment_id'=>$assignment->id,'student_id'=>$student->id],[
                'content'=>'پاسخ نمونه برای تست.','submitted_at'=>now()->subDay(),'score'=>92,'feedback'=>'پاسخ خوب است.','graded_at'=>now()->subHours(12),'graded_by'=>$teacher->id
            ]);
            $exam=Exam::firstOrCreate(['course_id'=>$course->id,'title'=>'آزمون دوره '.($i+1)],[
                'classroom_id'=>$classroom->id,'teacher_id'=>$teacher->id,'description'=>'آزمون نمونه برای تست کامل.','duration_minutes'=>45,
                'starts_at'=>now()->subDay(),'ends_at'=>now()->addDay(),'attempts_allowed'=>2,'status'=>'published'
            ]);
            $questions=collect();
            foreach([['2+2 چند می‌شود؟',['3','4','5','6'],'4'],['گزینه درست را انتخاب کنید.',['A','B','C','D'],'B']] as $qi=>$q){
                $questions->push(Question::firstOrCreate(['exam_id'=>$exam->id,'sort_order'=>$qi+1],[
                    'type'=>'multiple_choice','question'=>$q[0],'options'=>$q[1],'correct_answer'=>$q[2],'score'=>1,'sort_order'=>$qi+1
                ]));
            }
            $attempt=ExamAttempt::firstOrCreate(['exam_id'=>$exam->id,'student_id'=>$student->id,'attempt_number'=>1],[
                'started_at'=>now()->subHours(3),'submitted_at'=>now()->subHours(2),'score'=>2,'status'=>'submitted'
            ]);
            foreach($questions as $q){ExamAnswer::firstOrCreate(['exam_attempt_id'=>$attempt->id,'question_id'=>$q->id],['answer'=>$q->correct_answer,'is_correct'=>true,'score'=>$q->score]);}
        }

        $media=SeedMedia::make('math-handout',$owner->id,'application/pdf','pdf','learning-resources','private');
        LearningResource::firstOrCreate(
            ['academy_id'=>$academy->id,'course_id'=>$courses->first()->id,'title'=>'جزوه نمونه ریاضی'],
            ['classroom_id'=>$classrooms->first()->id,'lesson_id'=>$courses->first()->sections->first()->lessons->first()->id,'media_id'=>$media->id,'uploaded_by'=>$owner->id,
             'description'=>'جزوه قابل مدیریت از پنل.','resource_type'=>'pdf','visibility'=>'enrolled_students','release_at'=>now()->subDay(),'downloadable'=>true,'status'=>'published','sort_order'=>1]
        );
    }
}
