<?php
namespace Database\Seeders;

use App\Models\AcademicGrade;
use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicReferenceSeeder extends Seeder
{
    public function run(): void
    {
        foreach([
            ['code'=>'7','title'=>'پایه هفتم','sort_order'=>7],['code'=>'8','title'=>'پایه هشتم','sort_order'=>8],
            ['code'=>'9','title'=>'پایه نهم','sort_order'=>9],['code'=>'10','title'=>'پایه دهم','sort_order'=>10],
            ['code'=>'11','title'=>'پایه یازدهم','sort_order'=>11],['code'=>'12','title'=>'پایه دوازدهم','sort_order'=>12],
        ] as $grade){ AcademicGrade::firstOrCreate(['code'=>$grade['code']],$grade); }
        AcademicYear::firstOrCreate(['title'=>'1405-1406'],['start_date'=>'2026-09-23','end_date'=>'2027-09-22','is_current'=>true]);
    }
}
