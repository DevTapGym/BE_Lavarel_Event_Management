<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'logout', 'api_path' => '/v1/auth/logout', 'method' => 'POST', 'module' => 'Auth'],
            ['name' => 'get info', 'api_path' => '/v1/auth/me', 'method' => 'GET', 'module' => 'Auth'],
        ];

        $addedOrUpdated = false;

        foreach ($permissions as $perm) {
            $permission = Permission::updateOrCreate(
                ['name' => $perm['name']],
                $perm
            );

            if ($permission->wasRecentlyCreated) {
                $this->command->info("Permission created: {$perm['name']}");
                $addedOrUpdated = true;
            } elseif ($permission->wasChanged()) {
                $this->command->info("Permission updated: {$perm['name']}");
                $addedOrUpdated = true;
            }
        }

        if ($addedOrUpdated) {
            $this->command->info('Some permissions have been seeded successfully!');
        } else {
            $this->command->warn('Permissions already exist. Seeder skipped!');
        }
    }
}
