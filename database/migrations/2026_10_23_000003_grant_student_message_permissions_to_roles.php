<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('permissions')->insertOrIgnore([
            [
                'name' => 'student_messages.view',
                'label' => 'مشاهده مکاتبات دانش‌آموزان',
                'group' => 'student_messages',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'student_messages.manage',
                'label' => 'ارسال و پاسخ به مکاتبات دانش‌آموزان',
                'group' => 'student_messages',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $permissionIds = DB::table('permissions')
            ->whereIn('name', ['student_messages.view', 'student_messages.manage'])
            ->pluck('id', 'name');

        $roleIds = DB::table('roles')
            ->whereIn('slug', ['teacher', 'student'])
            ->pluck('id', 'slug');

        foreach (['teacher', 'student'] as $roleSlug) {
            $roleId = $roleIds[$roleSlug] ?? null;

            if (! $roleId) {
                continue;
            }

            foreach (['student_messages.view', 'student_messages.manage'] as $permissionName) {
                $permissionId = $permissionIds[$permissionName] ?? null;

                if ($permissionId) {
                    DB::table('permission_role')->insertOrIgnore([
                        'permission_id' => $permissionId,
                        'role_id' => $roleId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Additive authorization migration: retain permissions during rollback
        // so a rollback cannot silently remove access from a live application.
    }
};
