<?php
namespace Database\Seeders;
use App\Models\AcademicGrade;
use App\Models\AcademicYear;
use App\Models\ProductCategory;
use App\Models\LegalDocument;
use App\Models\AcademyContentCategory;
use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['code'=>'7','title'=>'پایه هفتم','sort_order'=>7],
            ['code'=>'8','title'=>'پایه هشتم','sort_order'=>8],
            ['code'=>'9','title'=>'پایه نهم','sort_order'=>9],
            ['code'=>'10','title'=>'پایه دهم','sort_order'=>10],
            ['code'=>'11','title'=>'پایه یازدهم','sort_order'=>11],
            ['code'=>'12','title'=>'پایه دوازدهم','sort_order'=>12],
        ] as $grade) {
            AcademicGrade::updateOrCreate(['code'=>$grade['code']],$grade);
        }

        AcademicYear::updateOrCreate(
            ['title'=>'1405-1406'],
            ['start_date'=>'2026-09-23','end_date'=>'2027-09-22','is_current'=>true]
        );

        foreach ([
            ['name'=>'کتابخانه','slug'=>'books'],
            ['name'=>'جزوه','slug'=>'booklets'],
            ['name'=>'آزمون','slug'=>'exams'],
        ] as $category) {
            ProductCategory::updateOrCreate(['slug'=>$category['slug']],$category);
        }

        foreach ([
            ['code'=>'purchase-terms','title'=>'شرایط خرید محصولات','version'=>'1.0','document_type'=>'terms_purchase','required_for_purchase'=>true],
            ['code'=>'copyright','title'=>'قوانین کپی‌رایت محصولات','version'=>'1.0','document_type'=>'copyright','required_for_purchase'=>true],
            ['code'=>'media-release','title'=>'رضایت انتشار محتوای رسانه‌ای','version'=>'1.0','document_type'=>'media_release','required_for_purchase'=>false],
        ] as $doc) {
            $content = 'متن این سند باید توسط مدیر آکادمی تکمیل و منتشر شود.';
            LegalDocument::updateOrCreate(
                ['code'=>$doc['code'],'version'=>$doc['version']],
                [...$doc,'content'=>$content,'content_hash'=>hash('sha256',$content),'is_active'=>true,'published_at'=>null]
            );
        }

        foreach ([
            ['title'=>'سخنی با اولیاء','slug'=>'parents'],
            ['title'=>'سخنی با دانش‌آموزان','slug'=>'students'],
            ['title'=>'سلام تیزهوشان','slug'=>'gifted'],
            ['title'=>'نکاتی از منابع خارجی','slug'=>'foreign-resources'],
            ['title'=>'اگر من طراح سوال بودم','slug'=>'question-designer'],
        ] as $category) {
            AcademyContentCategory::updateOrCreate(['academy_id'=>null,'slug'=>$category['slug']],$category);
        }
    }
}