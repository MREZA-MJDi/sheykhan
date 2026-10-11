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
        // Keep each demo teacher and student as a separate login so course scopes are visible.
        // Feature tests use these distinct demo identities plus any test-specific fixtures.
        if (! app()->environment('testing')) {
            $this->runDemoFoundation();
            return;
        }

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

    private function runDemoFoundation(): void
    {
        $owner = $this->user('owner@sheykhan.test', 'مدیر آکادمی شیخان', 'owner', 'academy-owner');

        $teacherDefinitions = [
            ['teacher.math@sheykhan.test', 'سمیه احمدی', 'teacher-math', 'ریاضی'],
            ['teacher.science@sheykhan.test', 'علی رضایی', 'teacher-science', 'علوم'],
            ['teacher.gifted@sheykhan.test', 'نگار کریمی', 'teacher-gifted', 'آمادگی تیزهوشان'],
        ];

        $teachers = collect();
        foreach ($teacherDefinitions as [$email, $name, $key, $specialization]) {
            $teacher = $this->user($email, $name, $key, 'teacher');

            TeacherProfile::firstOrCreate(['user_id' => $teacher->id], [
                'bio' => 'مدرس آکادمی شیخان با تمرکز بر آموزش مفهومی و حل مسئله.',
                'specialization' => $specialization,
                'education' => 'کارشناسی مرتبط',
                'experience_years' => 7,
                'is_verified' => true,
                'is_public' => true,
            ]);

            $teachers->push($teacher);
        }

        $studentDefinitions = [
            ['student.armin@sheykhan.test', 'آرین محمدی', 'student-armin', 'SH-1001', '7'],
            ['student.nika@sheykhan.test', 'نیکا حسینی', 'student-nika', 'SH-1002', '8'],
            ['student.parsa@sheykhan.test', 'پارسا رضایی', 'student-parsa', 'SH-1003', '9'],
            ['student.ava@sheykhan.test', 'آوا محمدی', 'student-ava', 'SH-1004', '7'],
            ['student.matin@sheykhan.test', 'متین احمدی', 'student-matin', 'SH-1005', '8'],
            ['student.tara@sheykhan.test', 'تارا کریمی', 'student-tara', 'SH-1006', '9'],
        ];

        $students = collect();
        foreach ($studentDefinitions as [$email, $name, $key, $number, $gradeCode]) {
            $student = $this->user($email, $name, $key, 'student');
            $grade = AcademicGrade::where('code', $gradeCode)->firstOrFail();

            StudentProfile::firstOrCreate(['user_id' => $student->id], [
                'student_number' => $number,
                'birth_date' => '2013-05-10',
                'grade' => $grade->title,
                'grade_id' => $grade->id,
                'school_name' => 'مدرسه منتخب شیخان',
                'bio' => 'دانش‌آموز نمونه آکادمی شیخان.',
                'registration_source' => 'admin',
                'onboarded_at' => now()->subMonths(2),
                'status' => 'active',
            ]);

            $students->push($student);
        }

        $parent = $this->user('parent.armin@sheykhan.test', 'مریم محمدی', 'parent-armin', 'parent');
        ParentProfile::firstOrCreate(['user_id' => $parent->id], [
            'occupation' => 'والد',
            'relation_default' => 'مادر',
        ]);
        $parent->children()->syncWithoutDetaching([
            $students->first()->id => ['relation' => 'مادر'],
        ]);

        $academy = Academy::firstOrCreate(['slug' => 'sheykhan-academy'], [
            'owner_id' => $owner->id,
            'name' => 'آکادمی شیخان',
            'code' => 'SHK-001',
            'description' => 'آکادمی آموزشی شیخان؛ آموزش تخصصی، کلاس آنلاین، آزمون و محتوای آموزشی.',
            'phone' => '02191000000',
            'email' => 'academy@sheykhan.test',
            'address' => 'تهران',
            'city' => 'تهران',
            'province' => 'تهران',
            'website' => 'https://sheykhan.test',
            'status' => 'active',
        ]);

        $members = [$owner->id => 'owner'];
        foreach ($teachers as $teacher) {
            $members[$teacher->id] = 'teacher';
        }
        foreach ($students as $student) {
            $members[$student->id] = 'student';
        }
        $members[$parent->id] = 'parent';

        foreach ($members as $userId => $role) {
            $academy->users()->syncWithoutDetaching([
                $userId => [
                    'role' => $role,
                    'status' => 'active',
                    'joined_at' => now(),
                ],
            ]);
        }
    }

    private function user(string $email,string $name,string $key,string $roleSlug): User
    {
        // Keep the documented demo credentials usable even when the user
        // already existed from an earlier seed with an old password/status.
        $seedPassword = env('SEED_USER_PASSWORD');
        if (blank($seedPassword) && ! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException(
                'Set SEED_USER_PASSWORD before seeding demo accounts outside local/testing environments.'
            );
        }
        $seedPassword = $seedPassword ?: 'Sheykhan@12345';

        $user = User::withTrashed()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'mobile' => '09' . str_pad((string) (1000000000 + abs(crc32($key)) % 899999999), 10, '0', STR_PAD_LEFT),
                'status' => 'active',
                'password' => Hash::make($seedPassword),
                'deleted_at' => null,
            ],
        );

        $user->roles()->syncWithoutDetaching([
            Role::where('slug', $roleSlug)->value('id'),
        ]);
        return $user;
    }
}
