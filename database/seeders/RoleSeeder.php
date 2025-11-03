<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $changed = false;

        $admin = Role::updateOrCreate(
            ['name' => 'ADMIN'],
            ['description' => 'Admin thì có tất cả quyền']
        );
        if ($admin->wasRecentlyCreated || $admin->wasChanged()) {
            $changed = true;
        }

        $allPermissions = Permission::pluck('name')->toArray();
        if ($admin->permissions !== $allPermissions) {
            $admin->permissions = $allPermissions;
            $admin->save();
            $changed = true;
        }
        if ($changed) {
            $this->command->info('Role ADMIN seeded successfully!');
        } else {
            $this->command->warn('No changes. Role ADMIN already up to date.');
        }
    }
}
