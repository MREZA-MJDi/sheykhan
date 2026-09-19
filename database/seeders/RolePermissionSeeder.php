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
            ['name' => 'dashboard.view', 'label' => 'مشاهده داشبورد', 'group' => 'dashboard'],

            ['name' => 'courses.view', 'label' => 'مشاهده دوره‌ها', 'group' => 'courses'],
            ['name' => 'courses.manage', 'label' => 'مدیریت دوره‌ها', 'group' => 'courses'],
            ['name' => 'lessons.view', 'label' => 'مشاهده درس‌ها', 'group' => 'lessons'],
            ['name' => 'lessons.manage', 'label' => 'مدیریت درس‌ها', 'group' => 'lessons'],

            ['name' => 'students.view', 'label' => 'مشاهده دانش‌آموزان', 'group' => 'students'],
            ['name' => 'students.manage', 'label' => 'مدیریت دانش‌آموزان', 'group' => 'students'],

            ['name' => 'teachers.view', 'label' => 'مشاهده مدرس‌ها', 'group' => 'teachers'],
            ['name' => 'teachers.manage', 'label' => 'مدیریت مدرس‌ها', 'group' => 'teachers'],

            ['name' => 'parents.view', 'label' => 'مشاهده والدین', 'group' => 'parents'],
            ['name' => 'parents.manage', 'label' => 'مدیریت والدین', 'group' => 'parents'],

            ['name' => 'assignments.view', 'label' => 'مشاهده تکالیف', 'group' => 'assignments'],
            ['name' => 'assignments.manage', 'label' => 'مدیریت تکالیف', 'group' => 'assignments'],

            ['name' => 'exams.view', 'label' => 'مشاهده آزمون‌ها', 'group' => 'exams'],
            ['name' => 'exams.manage', 'label' => 'مدیریت آزمون‌ها', 'group' => 'exams'],

            ['name' => 'blog.view', 'label' => 'مشاهده وبلاگ', 'group' => 'blog'],
            ['name' => 'blog.manage', 'label' => 'مدیریت وبلاگ', 'group' => 'blog'],

            ['name' => 'media.manage', 'label' => 'مدیریت رسانه', 'group' => 'media'],
            ['name' => 'reports.view', 'label' => 'مشاهده گزارش‌ها', 'group' => 'reports'],
            ['name' => 'settings.manage', 'label' => 'مدیریت تنظیمات', 'group' => 'settings'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        $rolePermissions = [
            'academy-owner' => collect($permissions)->pluck('name')->all(),

            'teacher' => [
                'dashboard.view',
                'courses.view',
                'courses.manage',
                'lessons.view',
                'lessons.manage',
                'students.view',
                'assignments.view',
                'assignments.manage',
                'exams.view',
                'exams.manage',
                'reports.view',
            ],

            'student' => [
                'dashboard.view',
                'courses.view',
                'lessons.view',
                'assignments.view',
                'exams.view',
            ],

            'parent' => [
                'dashboard.view',
                'courses.view',
                'lessons.view',
                'assignments.view',
                'exams.view',
                'reports.view',
            ],
        ];

        $roleMeta = [
            'academy-owner' => ['name' => 'مدیر آموزشگاه', 'description' => 'مدیریت کامل آموزشگاه'],
            'teacher' => ['name' => 'مدرس', 'description' => 'مدیریت محتوای آموزشی و دانش‌آموزان مرتبط'],
            'student' => ['name' => 'دانش‌آموز', 'description' => 'دسترسی به آموزش‌ها و فعالیت‌های آموزشی'],
            'parent' => ['name' => 'والد', 'description' => 'پیگیری وضعیت و فعالیت آموزشی فرزند'],
        ];

        foreach ($rolePermissions as $slug => $permissionNames) {
            $role = Role::updateOrCreate(
                ['slug' => $slug],
                $roleMeta[$slug]
            );

            $role->permissions()->sync(
                Permission::whereIn('name', $permissionNames)->pluck('id')
            );
        }
    }
}
