<?php
namespace Database\Seeders;

use App\Models\Academy;
use App\Models\AcademicGrade;
use App\Models\ParentProfile;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AcademyFoundationSeeder extends Seeder
{
    public function run(): void
    {
        $owner=$this->user('owner@sheykhan.test','مدیر آکادمی شیخان','owner','academy-owner');
        $teacherSeeds=[['teacher.math@sheykhan.test','سمیه احمدی','math'],['teacher.science@sheykhan.test','علی رضایی','science'],['teacher.gifted@sheykhan.test','نگار کریمی','gifted']];
        $teachers=collect($teacherSeeds)->map(fn($x)=>$this->user($x[0],$x[1],'teacher-'.$x[2],'teacher'));
        foreach($teachers as $t){TeacherProfile::firstOrCreate(['user_id'=>$t->id],['bio'=>'مدرس آکادمی شیخان با تمرکز بر آموزش مفهومی و حل مسئله.','specialization'=>'آموزش و حل مسئله','education'=>'کارشناسی مرتبط','experience_years'=>7,'is_verified'=>true]);}
        $studentSeeds=[
            ['student.armin@sheykhan.test','آرین محمدی','SH-1001','7'],['student.nika@sheykhan.test','نیکا حسینی','SH-1002','8'],
            ['student.parsa@sheykhan.test','پارسا رضایی','SH-1003','9'],['student.ava@sheykhan.test','آوا محمدی','SH-1004','7'],
            ['student.matin@sheykhan.test','متین احمدی','SH-1005','8'],['student.tara@sheykhan.test','تارا کریمی','SH-1006','9'],
        ];
        $students=collect();
        foreach($studentSeeds as [$email,$name,$number,$gradeCode]){
            $student=$this->user($email,$name,'student-'.$number,'student');
            $grade=AcademicGrade::where('code',$gradeCode)->firstOrFail();
            StudentProfile::firstOrCreate(['user_id'=>$student->id],['student_number'=>$number,'birth_date'=>'2013-05-10','grade'=>$grade->title,'grade_id'=>$grade->id,'school_name'=>'مدرسه منتخب شیخان','bio'=>'دانش‌آموز ثبت‌شده در آکادمی شیخان.','registration_source'=>'admin','onboarded_at'=>now()->subMonths(2),'status'=>'active']);
            $students->push($student);
        }
        $parents=collect([
            ['parent.armin@sheykhan.test','مریم محمدی','مادر',0],['parent.nika@sheykhan.test','حسین حسینی','پدر',1],['parent.parsa@sheykhan.test','الهام رضایی','مادر',2],
        ]);
        $parentUsers=collect();
        foreach($parents as [$email,$name,$relation,$index]){
            $parent=$this->user($email,$name,'parent-'.$index,'parent');
            ParentProfile::firstOrCreate(['user_id'=>$parent->id],['occupation'=>'والد','relation_default'=>$relation]);
            $parent->children()->syncWithoutDetaching([$students->get($index)->id=>['relation'=>strtolower($relation)]]);
            $parentUsers->push($parent);
        }
        $academy=Academy::firstOrCreate(['slug'=>'sheykhan-academy'],[
            'owner_id'=>$owner->id,'name'=>'آکادمی شیخان','code'=>'SHK-001','description'=>'آکادمی آموزشی شیخان؛ آموزش تخصصی، کلاس آنلاین، آزمون و محتوای آموزشی.',
            'phone'=>'02191000000','email'=>'academy@sheykhan.test','address'=>'تهران','city'=>'تهران','province'=>'تهران','website'=>'https://sheykhan.test','status'=>'active'
        ]);
        $members=[$owner->id=>'owner'];
        foreach($teachers as $u)$members[$u->id]='teacher';
        foreach($students as $u)$members[$u->id]='student';
        foreach($parentUsers as $u)$members[$u->id]='parent';
        foreach($members as $id=>$role){$academy->users()->syncWithoutDetaching([$id=>['role'=>$role,'status'=>'active','joined_at'=>now()]]);}
    }

    private function user(string $email,string $name,string $key,string $roleSlug): User
    {
        $user=User::firstOrCreate(['email'=>$email],[
            'name'=>$name,'mobile'=>'09'.str_pad((string)(1000000000+abs(crc32($key))%899999999),10,'0',STR_PAD_LEFT),
            'status'=>'active','password'=>Hash::make(env('SEED_USER_PASSWORD','Sheykhan@12345')),
        ]);
        $user->roles()->syncWithoutDetaching([Role::where('slug',$roleSlug)->value('id')]);
        return $user;
    }
}
