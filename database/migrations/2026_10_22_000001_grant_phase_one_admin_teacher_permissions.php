<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $permissions = [
            'exams.manage' => ['label' => 'مدیریت آزمون‌ها', 'group' => 'exams'],
            'attendance.manage' => ['label' => 'ثبت و ویرایش حضور و غیاب', 'group' => 'attendance'],
            'parent_messages.view' => ['label' => 'مشاهده پیام‌های اولیا', 'group' => 'parent_messages'],
            'teacher_payments.view' => ['label' => 'مشاهده حقوق ثبت‌شده مدرس‌ها', 'group' => 'teacher_payments'],
            'teacher_payments.manage' => ['label' => 'مدیریت سندهای پرداخت مدرس‌ها', 'group' => 'teacher_payments'],
        ];

        foreach ($permissions as $name => $metadata) {
            DB::table('permissions')->insertOrIgnore([
                'name' => $name,
                'label' => $metadata['label'],
                'group' => $metadata['group'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $grants = [
            'academy-owner' => [
                'exams.manage',
                'attendance.manage',
                'live_classes.manage',
                'parents.manage',
                'parent_messages.view',
                'teacher_payments.view',
                'teacher_payments.manage',
            ],
            'teacher' => [
                'exams.manage',
                'attendance.manage',
                'live_classes.manage',
                'parent_messages.view',
                'teacher_payments.view',
            ],
        ];

        foreach ($grants as $roleSlug => $permissionNames) {
            $roleId = DB::table('roles')->where('slug', $roleSlug)->value('id');
            if (! $roleId) {
                continue;
            }

            $permissionIds = DB::table('permissions')
                ->whereIn('name', $permissionNames)
                ->pluck('id');

            foreach ($permissionIds as $permissionId) {
                DB::table('permission_role')->insertOrIgnore([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Access grants are intentionally not revoked automatically on rollback:
        // deployments may already depend on these additive permissions.
    }
};
