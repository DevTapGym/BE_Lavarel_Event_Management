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

        // Lấy tất cả permissions dưới dạng mảng string
        $allPermissions = Permission::pluck('name')->toArray();

        // Tạo hoặc cập nhật role ADMIN
        $admin = Role::updateOrCreate(
            ['name' => 'ADMIN'],
            [
                'description' => 'Admin thì có tất cả quyền',
                'permissions' => $allPermissions
            ]
        );

        if ($admin->wasRecentlyCreated || $admin->wasChanged()) {
            $changed = true;
        }

        // Kiểm tra và cập nhật permissions nếu khác
        $currentPermissions = $admin->permissions ?? [];
        sort($allPermissions);
        sort($currentPermissions);

        if ($currentPermissions !== $allPermissions) {
            $admin->permissions = $allPermissions;
            $admin->save();
            $changed = true;
            $this->command->info('Updated ADMIN permissions: ' . count($allPermissions) . ' permissions');
        }

        if ($changed) {
            $this->command->info('Role ADMIN seeded successfully!');
        } else {
            $this->command->warn('No changes. Role ADMIN already up to date.');
        }
    }
}
