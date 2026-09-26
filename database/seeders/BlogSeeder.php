<?php
namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\User;
use Database\Seeders\Support\SeedMedia;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
 public function run(): void
 {
  $owner=User::where('email','owner@sheykhan.test')->firstOrFail();
  $category=BlogCategory::firstOrCreate(['slug'=>'learning'],['name'=>'یادگیری','description'=>'مقالات آموزشی و مهارت مطالعه']);
  $tags=collect([BlogTag::firstOrCreate(['slug'=>'study-skills'],['name'=>'مهارت مطالعه']),BlogTag::firstOrCreate(['slug'=>'gifted'],['name'=>'تیزهوشان']),BlogTag::firstOrCreate(['slug'=>'exam-prep'],['name'=>'آمادگی آزمون'])]);
  foreach([['effective-study-plan','چطور برنامه مطالعه مؤثر بسازیم؟'],['gifted-exam-strategy','استراتژی حل سوالات تیزهوشان'],['parent-learning-support','نقش والدین در مسیر یادگیری']] as [$slug,$title]){
   $post=BlogPost::firstOrCreate(['slug'=>$slug],['category_id'=>$category->id,'author_id'=>$owner->id,'title'=>$title,'excerpt'=>'مقاله آموزشی اولیه برای تست وبلاگ.','content'=>'این مقاله توسط Seeder ایجاد شده و از پنل قابل ویرایش و انتشار است.','status'=>'published','published_at'=>now()->subDays(4)]);
   $post->tags()->syncWithoutDetaching($tags->pluck('id')->all());
   $media=SeedMedia::make('blog-'.$slug,$owner->id,'image/svg+xml','svg','blog','public');
   $post->media()->syncWithoutDetaching([$media->id=>['collection'=>'featured','sort_order'=>0,'is_featured'=>true]]);
  }
 }
}
