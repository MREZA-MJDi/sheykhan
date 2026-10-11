<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

final class ManageSeoSpecialistAccess extends Command
{
    protected $signature = 'sheykhan:seo-access {email : Email of an existing user account} {action=grant : grant or revoke}';

    protected $description = 'Grant or revoke least-privilege SEO specialist access for an existing active account.';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));
        $action = strtolower(trim((string) $this->argument('action')));

        if (! in_array($action, ['grant', 'revoke'], true)) {
            $this->error('Action must be either "grant" or "revoke".');

            return self::INVALID;
        }

        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user) {
            $this->error('No existing account was found for that email. No user was created.');

            return self::FAILURE;
        }

        if ($action === 'revoke') {
            $role = Role::query()->where('slug', 'seo-specialist')->first();

            if (! $role || ! $user->roles()->whereKey($role->getKey())->exists()) {
                $this->info('This account has no SEO specialist role.');

                return self::SUCCESS;
            }

            $user->roles()->detach($role->getKey());
            $this->info("SEO specialist access revoked for {$user->email}.");

            return self::SUCCESS;
        }

        if ($user->status !== 'active' || $user->trashed()) {
            $this->error('The account is not active. Activate and verify the account before granting access.');

            return self::FAILURE;
        }

        $dashboardPermission = Permission::query()->firstOrCreate(
            ['name' => 'dashboard.view'],
            ['label' => 'مشاهده داشبورد', 'group' => 'dashboard']
        );
        $seoPermission = Permission::query()->firstOrCreate(
            ['name' => 'seo.manage'],
            ['label' => 'مدیریت SEO', 'group' => 'seo']
        );

        $role = Role::query()->updateOrCreate(
            ['slug' => 'seo-specialist'],
            ['name' => 'متخصص SEO', 'description' => 'مدیریت SEO صفحات عمومی سایت بدون دسترسی به عملیات آموزشگاه']
        );

        // Enforce least privilege even if the role already existed with extra permissions.
        $role->permissions()->sync([$dashboardPermission->getKey(), $seoPermission->getKey()]);
        $user->roles()->syncWithoutDetaching([$role->getKey()]);

        $this->info("SEO specialist role granted to {$user->email}.");
        $this->line('Permissions attached to this role: dashboard.view, seo.manage.');
        $this->line('No user account was created; existing roles and permissions on this account were left unchanged.');

        return self::SUCCESS;
    }
}
