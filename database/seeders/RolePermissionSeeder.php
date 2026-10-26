<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name'=>'dashboard.view','label'=>'مشاهده داشبورد','group'=>'dashboard'],

            ['name'=>'academy.view','label'=>'مشاهده آموزشگاه','group'=>'academy'],
            ['name'=>'academy.manage','label'=>'مدیریت آموزشگاه','group'=>'academy'],

            ['name'=>'courses.view','label'=>'مشاهده دوره‌ها','group'=>'courses'],
            ['name'=>'courses.manage','label'=>'مدیریت دوره‌ها','group'=>'courses'],
            ['name'=>'lessons.view','label'=>'مشاهده درس‌ها','group'=>'lessons'],
            ['name'=>'lessons.progress','label'=>'ثبت پیشرفت درس','group'=>'lessons'],
            ['name'=>'lessons.manage','label'=>'مدیریت درس‌ها','group'=>'lessons'],

            ['name'=>'classrooms.view','label'=>'مشاهده کلاس‌ها','group'=>'classrooms'],
            ['name'=>'classrooms.manage','label'=>'مدیریت کلاس‌ها','group'=>'classrooms'],
            ['name'=>'enrollments.view','label'=>'مشاهده ثبت‌نام‌ها','group'=>'enrollments'],
            ['name'=>'enrollments.manage','label'=>'مدیریت ثبت‌نام‌ها','group'=>'enrollments'],

            ['name'=>'students.view','label'=>'مشاهده دانش‌آموزان','group'=>'students'],
            ['name'=>'students.manage','label'=>'مدیریت دانش‌آموزان','group'=>'students'],
            ['name'=>'teachers.view','label'=>'مشاهده مدرس‌ها','group'=>'teachers'],
            ['name'=>'teachers.manage','label'=>'مدیریت مدرس‌ها','group'=>'teachers'],
            ['name'=>'parents.view','label'=>'مشاهده والدین','group'=>'parents'],
            ['name'=>'parents.manage','label'=>'مدیریت والدین','group'=>'parents'],
            ['name'=>'parent_messages.view','label'=>'مشاهده پیام‌های اولیا','group'=>'parent_messages'],
            ['name'=>'parent_messages.manage','label'=>'پاسخ به پیام‌های اولیا','group'=>'parent_messages'],
            ['name'=>'student_messages.view','label'=>'مشاهده مکاتبات دانش‌آموزان','group'=>'student_messages'],
            ['name'=>'student_messages.manage','label'=>'ارسال و پاسخ به مکاتبات دانش‌آموزان','group'=>'student_messages'],
            ['name'=>'teacher_payments.view','label'=>'مشاهده حقوق ثبت‌شده مدرس‌ها','group'=>'teacher_payments'],
            ['name'=>'teacher_payments.manage','label'=>'مدیریت سندهای پرداخت مدرس‌ها','group'=>'teacher_payments'],

            ['name'=>'assignments.view','label'=>'مشاهده تکالیف','group'=>'assignments'],
            ['name'=>'assignments.submit','label'=>'ارسال تکلیف','group'=>'assignments'],
            ['name'=>'assignments.manage','label'=>'مدیریت تکالیف','group'=>'assignments'],
            ['name'=>'exams.view','label'=>'مشاهده آزمون‌ها','group'=>'exams'],
            ['name'=>'exams.attempt','label'=>'شرکت در آزمون','group'=>'exams'],
            ['name'=>'exams.manage','label'=>'مدیریت آزمون‌ها','group'=>'exams'],
            ['name'=>'attendance.view','label'=>'مشاهده حضور و غیاب','group'=>'attendance'],
            ['name'=>'attendance.manage','label'=>'مدیریت حضور و غیاب','group'=>'attendance'],

            ['name'=>'live_classes.view','label'=>'مشاهده کلاس‌های آنلاین','group'=>'live_classes'],
            ['name'=>'live_classes.manage','label'=>'مدیریت کلاس‌های آنلاین','group'=>'live_classes'],
            ['name'=>'reports.view','label'=>'مشاهده گزارش‌ها','group'=>'reports'],
            ['name'=>'results.view','label'=>'مشاهده نتایج آموزشی','group'=>'results'],
            ['name'=>'notes.view','label'=>'مشاهده یادداشت‌ها','group'=>'notes'],
            ['name'=>'notes.manage','label'=>'مدیریت یادداشت‌های شخصی','group'=>'notes'],
            ['name'=>'profile.view','label'=>'مشاهده پروفایل شخصی','group'=>'profile'],
            ['name'=>'profile.manage','label'=>'ویرایش پروفایل شخصی','group'=>'profile'],
            ['name'=>'profile.security','label'=>'مدیریت امنیت حساب شخصی','group'=>'profile'],

            ['name'=>'media.view','label'=>'مشاهده رسانه','group'=>'media'],
            ['name'=>'media.upload','label'=>'آپلود رسانه','group'=>'media'],
            ['name'=>'media.download','label'=>'دانلود رسانه','group'=>'media'],
            ['name'=>'media.manage','label'=>'مدیریت رسانه','group'=>'media'],

            ['name'=>'blog.view','label'=>'مشاهده وبلاگ','group'=>'blog'],
            ['name'=>'blog.manage','label'=>'مدیریت وبلاگ','group'=>'blog'],
            ['name'=>'seo.manage','label'=>'مدیریت SEO','group'=>'seo'],
            ['name'=>'settings.manage','label'=>'مدیریت تنظیمات','group'=>'settings'],

            ['name'=>'resources.view','label'=>'مشاهده منابع آموزشی','group'=>'resources'],
            ['name'=>'resources.manage','label'=>'مدیریت منابع آموزشی','group'=>'resources'],
            ['name'=>'products.view','label'=>'مشاهده محصولات','group'=>'products'],
            ['name'=>'products.manage','label'=>'مدیریت محصولات','group'=>'products'],
            ['name'=>'orders.view','label'=>'مشاهده سفارش‌ها','group'=>'orders'],
            ['name'=>'orders.manage','label'=>'مدیریت سفارش‌ها','group'=>'orders'],
            ['name'=>'payments.view','label'=>'مشاهده پرداخت‌ها','group'=>'payments'],
            ['name'=>'finance.view','label'=>'مشاهده امور مالی','group'=>'finance'],
            ['name'=>'achievements.view','label'=>'مشاهده افتخارآفرینان','group'=>'achievements'],
            ['name'=>'achievements.manage','label'=>'مدیریت افتخارآفرینان','group'=>'achievements'],
            ['name'=>'testimonials.view','label'=>'مشاهده رضایتمندی','group'=>'testimonials'],
            ['name'=>'testimonials.manage','label'=>'مدیریت رضایتمندی','group'=>'testimonials'],
            ['name'=>'legal.manage','label'=>'مدیریت اسناد حقوقی','group'=>'legal'],
            ['name'=>'content.manage','label'=>'مدیریت محتوای آکادمی','group'=>'content'],
            ['name'=>'onboarding.manage','label'=>'مدیریت ثبت‌نام دانش‌آموزان قدیمی','group'=>'onboarding'],
            ['name'=>'audit.view','label'=>'مشاهده گزارش فعالیت‌ها','group'=>'audit'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name'=>$permission['name']], $permission);
        }

        $rolePermissions = [
            // Academy Owner is an academy-scoped operator, not a platform super-admin.
            // Keep platform-only capabilities explicit so adding a future route does not
            // silently grant the Owner access to it.
            'academy-owner' => [
                'dashboard.view',
                'academy.view','academy.manage',
                'courses.view','courses.manage',
                'lessons.view',
                'classrooms.view','classrooms.manage',
                'enrollments.view','enrollments.manage',
                'students.view','students.manage',
                'onboarding.manage',
                'teachers.view','teachers.manage',
                'parents.view','parents.manage','parent_messages.view',
                'assignments.view',
                'exams.view','exams.manage',
                'attendance.view','attendance.manage',
                'live_classes.view','live_classes.manage',
                'reports.view',
                'profile.view','profile.manage',
                'media.view','media.upload','media.download','media.manage',
                'resources.view','resources.manage',
                'products.view','products.manage',
                'orders.view','orders.manage','payments.view','finance.view',
                'legal.manage',
                'teacher_payments.view','teacher_payments.manage',
                'audit.view',
            ],

            'seo-specialist' => [
                'dashboard.view','seo.manage',
            ],

            'teacher' => [
                'dashboard.view','academy.view',
                'courses.view','courses.manage',
                'lessons.view','lessons.manage',
                'classrooms.view','classrooms.manage',
                'students.view',
                'assignments.view','assignments.manage',
                'parent_messages.view','parent_messages.manage',
                'student_messages.view','student_messages.manage',
                'teacher_payments.view',
                'exams.view','exams.manage',
                'attendance.view','attendance.manage',
                'live_classes.view','live_classes.manage',
                'reports.view',
                'profile.view','profile.manage',
                'media.view','media.upload','media.download','resources.view','resources.manage',
            ],

            'student' => [
                'dashboard.view','academy.view',
                'courses.view','lessons.view','lessons.progress',
                'student_messages.view','student_messages.manage',
                'classrooms.view',
                'assignments.view','assignments.submit',
                'exams.view','exams.attempt',
                'attendance.view',
                'live_classes.view',
                'results.view',
                'notes.view','notes.manage',
                'profile.view','profile.manage','profile.security',
                'achievements.view',
                'media.view','resources.view',
            ],

            'parent' => [
                'dashboard.view','academy.view',
                'courses.view','lessons.view',
                'classrooms.view',
                'assignments.view',
                'exams.view',
                'attendance.view',
                'live_classes.view',
                'reports.view',
                'media.view','media.download',
            ],
        ];

        $roleMeta = [
            'academy-owner'=>['name'=>'ادمین / منشی آکادمی','description'=>'مدیریت عملیات آموزشگاه، ارتباط با خانواده‌ها و پیگیری امور آموزشی'],
            'seo-specialist'=>['name'=>'متخصص SEO','description'=>'مدیریت SEO صفحات عمومی سایت بدون دسترسی به عملیات آموزشگاه'],
            'teacher'=>['name'=>'مدرس','description'=>'مدیریت محتوای آموزشی و فعالیت‌های آموزشی مرتبط'],
            'student'=>['name'=>'دانش‌آموز','description'=>'دسترسی به آموزش‌ها و فعالیت‌های شخصی آموزشی'],
            'parent'=>['name'=>'والد','description'=>'پیگیری آموزش، حضور و عملکرد فرزند'],
        ];

        foreach ($rolePermissions as $slug => $permissionNames) {
            $role = Role::updateOrCreate(['slug'=>$slug], $roleMeta[$slug]);

            $role->permissions()->sync(
                Permission::whereIn('name',$permissionNames)->pluck('id')
            );
        }
    }
}
