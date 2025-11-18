<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

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
                'permissions' => $allPermissions,
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
            $this->command->info('Updated ADMIN permissions: '.count($allPermissions).' permissions');
        }

        // Tạo hoặc cập nhật role ORGANIZER (Người tổ chức)
        $organizerPermissions = [
            'logout',
            'get info',
            'edit profile',
            'change password',

            'create-event',
            'update-event',
            'delete-event',
            'cancel-event',

            'add-event-status',

            'view-locations',
            'view-detail-locations',

            'view-papers',
            'view-detail-papers',
            'create-paper',
            'update-paper',
            'delete-paper',

            'view-registrations',
            'check-in-registration',

            'get notifications by event',
            'get all notifications',
            'create notification',
            'update notification',
            'delete notification',

            'upload avatar',
            'upload speaker avatar',
            'upload event image',
            'upload paper file',

        ];

        $organizer = Role::updateOrCreate(
            ['name' => 'ORGANIZER'],
            [
                'description' => 'Người tổ chức sự kiện - Quản lý sự kiện và thông báo',
                'permissions' => $organizerPermissions,
            ]
        );

        if ($organizer->wasRecentlyCreated || $organizer->wasChanged()) {
            $changed = true;
            $this->command->info('Role ORGANIZER seeded with '.count($organizerPermissions).' permissions');
        }

        // Kiểm tra và cập nhật organizer permissions nếu khác
        $currentOrganizerPermissions = $organizer->permissions ?? [];
        sort($organizerPermissions);
        sort($currentOrganizerPermissions);

        if ($currentOrganizerPermissions !== $organizerPermissions) {
            $organizer->permissions = $organizerPermissions;
            $organizer->save();
            $changed = true;
            $this->command->info('Updated ORGANIZER permissions: '.count($organizerPermissions).' permissions');
        }

        // Tạo hoặc cập nhật role USER (Người dùng thường)
        $userPermissions = [
            'logout',
            'get info',
            'edit profile',
            'change password',

            'view-registrations-by-user',
            'create-registration',
            'cancel-registration',

            'view-feedbacks',
            'view-feedbacks-by-user',
            'create-feedback',
            'update-feedback',
            'delete-feedback',

            'view-papers',
            'view-detail-papers',

            'download paper',
        ];

        $user = Role::updateOrCreate(
            ['name' => 'USER'],
            [
                'description' => 'Người dùng thường - Không có quyền đặc biệt',
                'permissions' => $userPermissions,
            ]
        );

        if ($user->wasRecentlyCreated || $user->wasChanged()) {
            $changed = true;
            $this->command->info('Role USER seeded with '.count($userPermissions).' permissions');
        }

        if ($changed) {
            $this->command->info('Roles seeded successfully!');
        } else {
            $this->command->warn('No changes. All roles already up to date.');
        }
    }
}
