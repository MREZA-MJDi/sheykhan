<?php
namespace Database\Seeders;
use App\Models\Academy;
use App\Models\SeoMeta;
use App\Models\Setting;
use Illuminate\Database\Seeder;
class SystemSeeder extends Seeder
{
 public function run(): void
 {
  $academy=Academy::where('slug','sheykhan-academy')->firstOrFail();
  $title='آکادمی شیخان | آموزش آنلاین';
  $description='آکادمی شیخان؛ آموزش آنلاین، آزمون و محتوای آموزشی.';
  SeoMeta::firstOrCreate(['seoable_type'=>get_class($academy),'seoable_id'=>$academy->id],['title'=>$title,'description'=>$description,'keywords'=>'آکادمی شیخان, آموزش آنلاین, تیزهوشان','robots'=>'index,follow','og_title'=>$title,'og_description'=>$description]);
  foreach(['academy.name'=>['value'=>$academy->name,'type'=>'string','group'=>'academy','is_public'=>true],'academy.phone'=>['value'=>$academy->phone,'type'=>'string','group'=>'academy','is_public'=>true],'academy.default_currency'=>['value'=>'IRR','type'=>'string','group'=>'commerce','is_public'=>false]] as $key=>$data) Setting::firstOrCreate(['key'=>$key],$data);
 }
}
