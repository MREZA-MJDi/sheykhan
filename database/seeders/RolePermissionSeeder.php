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

            ['name'=>'assignments.view','label'=>'مشاهده تکالیف','group'=>'assignments'],
            ['name'=>'assignments.manage','label'=>'مدیریت تکالیف','group'=>'assignments'],
            ['name'=>'exams.view','label'=>'مشاهده آزمون‌ها','group'=>'exams'],
            ['name'=>'exams.manage','label'=>'مدیریت آزمون‌ها','group'=>'exams'],
            ['name'=>'attendance.view','label'=>'مشاهده حضور و غیاب','group'=>'attendance'],
            ['name'=>'attendance.manage','label'=>'مدیریت حضور و غیاب','group'=>'attendance'],

            ['name'=>'live_classes.view','label'=>'مشاهده کلاس‌های آنلاین','group'=>'live_classes'],
            ['name'=>'live_classes.manage','label'=>'مدیریت کلاس‌های آنلاین','group'=>'live_classes'],
            ['name'=>'reports.view','label'=>'مشاهده گزارش‌ها','group'=>'reports'],

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

        $all = collect($permissions)->pluck('name')->all();

        $rolePermissions = [
            'academy-owner' => $all,

            'teacher' => [
                'dashboard.view','academy.view',
                'courses.view','courses.manage',
                'lessons.view','lessons.manage',
                'classrooms.view',
                'students.view',
                'assignments.view','assignments.manage',
                'exams.view','exams.manage',
                'attendance.view','attendance.manage',
                'live_classes.view','live_classes.manage',
                'reports.view',
                'media.view','media.upload','media.download','resources.view','resources.manage',
            ],

            'student' => [
                'dashboard.view','academy.view',
                'courses.view','lessons.view',
                'classrooms.view',
                'assignments.view',
                'exams.view',
                'attendance.view',
                'live_classes.view',
                'reports.view',
                'media.view','media.download','resources.view',
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
            'academy-owner'=>['name'=>'مدیر آموزشگاه','description'=>'مدیریت کامل آموزشگاه'],
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
