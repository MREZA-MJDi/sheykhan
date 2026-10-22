<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $permissionId = DB::table('permissions')
            ->where('name', 'live_classes.manage')
            ->value('id');

        if (! $permissionId) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => 'live_classes.manage',
                'label' => 'مدیریت کلاس‌های آنلاین',
                'group' => 'live_classes',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $ownerRoleId = DB::table('roles')
            ->where('slug', 'academy-owner')
            ->value('id');

        if ($ownerRoleId) {
            DB::table('permission_role')->updateOrInsert(
                [
                    'permission_id' => $permissionId,
                    'role_id' => $ownerRoleId,
                ],
                []
            );
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')
            ->where('name', 'live_classes.manage')
            ->value('id');

        $ownerRoleId = DB::table('roles')
            ->where('slug', 'academy-owner')
            ->value('id');

        if ($permissionId && $ownerRoleId) {
            DB::table('permission_role')
                ->where('permission_id', $permissionId)
                ->where('role_id', $ownerRoleId)
                ->delete();
        }
    }
};
