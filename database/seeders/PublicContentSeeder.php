<?php
namespace Database\Seeders;

use App\Models\Academy;
use App\Models\AcademyContent;
use App\Models\AcademyContentCategory;
use App\Models\Achievement;
use App\Models\AcademicYear;
use App\Models\Testimonial;
use App\Models\User;
use Database\Seeders\Support\SeedMedia;
use Illuminate\Database\Seeder;

class PublicContentSeeder extends Seeder
{
 public function run(): void
 {
  $academy=Academy::where('slug','sheykhan-academy')->firstOrFail(); $owner=User::where('email','owner@sheykhan.test')->firstOrFail(); $year=AcademicYear::where('title','1405-1406')->firstOrFail();
  $students=User::whereIn('email',['student.armin@sheykhan.test','student.nika@sheykhan.test','student.parsa@sheykhan.test'])->get();
  $parents=User::whereIn('email',['parent.armin@sheykhan.test','parent.nika@sheykhan.test'])->get();
  foreach([
   ['parents','سخنی با اولیاء'],['students','سخنی با دانش‌آموزان'],['gifted','سلام تیزهوشان'],
   ['foreign-resources','نکاتی از سوالات منابع خارجی'],['question-designer','اگر من طراح سوال بودم']
  ] as $cIndex=>$c){
   $cat=AcademyContentCategory::firstOrCreate(['academy_id'=>$academy->id,'slug'=>$c[0]],['title'=>$c[1],'sort_order'=>$cIndex+1,'is_active'=>true]);
   foreach(['article','video'] as $type) for($i=1;$i<=3;$i++){
    $content=AcademyContent::firstOrCreate(['academy_id'=>$academy->id,'slug'=>$c[0].'-'.$type.'-'.$i],[
     'category_id'=>$cat->id,'type'=>$type,'title'=>$c[1].' - '.($type==='video'?'ویدئو':'مقاله').' '.$i,
     'excerpt'=>'محتوای رسمی آکادمی شیخان.','body'=>'این رکورد توسط Seeder ایجاد شده و از پنل قابل ویرایش و انتشار است.',
     'video_duration_seconds'=>$type==='video'?240:null,'status'=>'published','is_featured'=>$i===1,'sort_order'=>$i,'published_at'=>now()->subDays($i),'created_by'=>$owner->id
    ]);
    $media=SeedMedia::make('academy-content-'.$c[0].'-'.$type.'-'.$i,$owner->id,$type==='video'?'video/mp4':'image/svg+xml',$type==='video'?'mp4':'svg','academy-content',$type==='video'?'private':'public');
    $content->media()->syncWithoutDetaching([$media->id=>['collection'=>$type,'sort_order'=>0,'is_featured'=>true]]);
   }
  }
  foreach([
   [$students->get(0),'gifted_school','علامه حلی ۱'],[$students->get(1),'sample_school','مدرسه نمونه دولتی فرزانگان'],[$students->get(2),'gifted_school','علامه حلی ۲']
  ] as $i=>$row){
   [$student,$type,$school]=$row; $media=SeedMedia::make('achievement-'.($i+1),$owner->id,'image/svg+xml','svg','achievements','public');
   Achievement::firstOrCreate(['academy_id'=>$academy->id,'student_id'=>$student->id,'title'=>'افتخارآفرین شماره '.($i+1)],[
    'display_name'=>$student->name,'achievement_type'=>$type,'school_name'=>$school,'grade_id'=>$student->studentProfile?->grade_id,'academic_year_id'=>$year->id,
    'description'=>'رکورد اولیه برای نمایش افتخارآفرینان.','media_id'=>$media->id,'status'=>'published','is_featured'=>true,'published_at'=>now()->subDays($i),'created_by'=>$owner->id
   ]);
  }
  foreach([
   ['مریم محمدی','parent',$parents->get(0),'از روند کلاس‌ها و پیگیری مدرس‌ها رضایت دارم.'],
   ['آرین محمدی','student',$students->get(0),'جلسات ضبط‌شده برای مرور مجدد خیلی کاربردی است.'],
   ['حسین حسینی','parent',$parents->get(1),'گزارش پیشرفت و آزمون‌ها برای ما مفید است.']
  ] as $i=>$d){
   $t=Testimonial::firstOrCreate(['academy_id'=>$academy->id,'display_name'=>$d[0]],['user_id'=>$d[2]?->id,'role'=>$d[1],'content_text'=>$d[3],'status'=>'approved','is_featured'=>true,'sort_order'=>$i+1,'published_at'=>now()->subDays(2-$i)]);
   $image=SeedMedia::make('testimonial-'.$i.'-image',$owner->id,'image/svg+xml','svg','testimonials','public');
   $audio=SeedMedia::make('testimonial-'.$i.'-audio',$owner->id,'audio/mpeg','mp3','testimonials','private');
   $video=SeedMedia::make('testimonial-'.$i.'-video',$owner->id,'video/mp4','mp4','testimonials','private');
   $t->media()->syncWithoutDetaching([$image->id=>['collection'=>'image','sort_order'=>0,'is_featured'=>true],$audio->id=>['collection'=>'audio','sort_order'=>1],$video->id=>['collection'=>'video','sort_order'=>2]]);
  }
 }
}
