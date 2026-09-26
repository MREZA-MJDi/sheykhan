<?php
namespace Database\Seeders;
use App\Models\Academy;
use App\Models\AcademicGrade;
use App\Models\StudentOnboarding;
use App\Models\User;
use Illuminate\Database\Seeder;
class OnboardingSeeder extends Seeder
{
 public function run(): void
 {
  $academy=Academy::where('slug','sheykhan-academy')->firstOrFail();
  $owner=User::where('email','owner@sheykhan.test')->firstOrFail();
  $student=User::where('email','student.matin@sheykhan.test')->firstOrFail();
  $grade=AcademicGrade::where('code','8')->firstOrFail();
  $lookup=hash_hmac('sha256','0012345678',config('app.key'));
  StudentOnboarding::firstOrCreate(['academy_id'=>$academy->id,'national_id_lookup'=>$lookup],['admin_id'=>$owner->id,'student_id'=>$student->id,'entered_name'=>$student->name,'requested_grade_id'=>$grade->id,'school_name'=>'مدرسه منتخب شیخان','mobile'=>$student->mobile,'source'=>'legacy','status'=>'activated','notes'=>'رکورد تست ورود دانش‌آموز قدیمی.','verified_at'=>now()->subDays(10),'activated_at'=>now()->subDays(9)]);
 }
}
