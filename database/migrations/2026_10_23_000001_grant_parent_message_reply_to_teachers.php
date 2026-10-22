<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('permissions')->insertOrIgnore([
            'name' => 'parent_messages.manage',
            'label' => 'پاسخ به پیام‌های اولیا',
            'group' => 'parent_messages',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $permissionId = DB::table('permissions')
            ->where('name', 'parent_messages.manage')
            ->value('id');

        $teacherRoleId = DB::table('roles')
            ->where('slug', 'teacher')
            ->value('id');

        if ($permissionId && $teacherRoleId) {
            DB::table('permission_role')->insertOrIgnore([
                'permission_id' => $permissionId,
                'role_id' => $teacherRoleId,
            ]);
        }
    }

    public function down(): void
    {
        // This is an additive authorization migration. Leave the permission intact on rollback
        // so a rollback cannot silently remove access needed by a newer application instance.
    }
};
