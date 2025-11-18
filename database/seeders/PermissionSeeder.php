<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'logout', 'api_path' => '/v1/auth/logout', 'method' => 'POST', 'module' => 'Auth'],
            ['name' => 'get info', 'api_path' => '/v1/auth/me', 'method' => 'GET', 'module' => 'Auth'],
            ['name' => 'edit profile', 'api_path' => '/v1/auth/edit-profile', 'method' => 'PUT', 'module' => 'Auth'],
            ['name' => 'change password', 'api_path' => '/v1/auth/change-password', 'method' => 'PUT', 'module' => 'Auth'],

            ['name' => 'get notifications by event', 'api_path' => '/v1/notification/{eventId}', 'method' => 'GET', 'module' => 'Notification'],
            ['name' => 'get all notifications', 'api_path' => '/v1/notification', 'method' => 'GET', 'module' => 'Notification'],
            ['name' => 'create notification', 'api_path' => '/v1/notification', 'method' => 'POST', 'module' => 'Notification'],
            ['name' => 'update notification', 'api_path' => '/v1/notification/{id}', 'method' => 'PUT', 'module' => 'Notification'],
            ['name' => 'delete notification', 'api_path' => '/v1/notification/{id}', 'method' => 'DELETE', 'module' => 'Notification'],

            ['name' => 'view-feedbacks', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Feedback'],
            ['name' => 'view-feedbacks-by-user', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Feedback'],
            ['name' => 'create-feedback', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Feedback'],
            ['name' => 'update-feedback', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Feedback'],
            ['name' => 'delete-feedback', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Feedback'],

            ['name' => 'view-events', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Event'],
            ['name' => 'view-detail-events', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Event'],
            ['name' => 'create-event', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Event'],
            ['name' => 'update-event', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Event'],
            ['name' => 'delete-event', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Event'],
            ['name' => 'cancel-event', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Event'],
            ['name' => 'add-event-status', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Event'],
            ['name' => 'approve-event', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Event'],

            ['name' => 'view-user-history-points', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'HistoryPoints'],
            ['name' => 'view-event-history-points', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'HistoryPoints'],

            ['name' => 'view-locations', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Location'],
            ['name' => 'view-detail-locations', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Location'],
            ['name' => 'create-location', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Location'],
            ['name' => 'update-location', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Location'],
            ['name' => 'delete-location', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Location'],

            ['name' => 'view-papers', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Paper'],
            ['name' => 'view-detail-papers', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Paper'],
            ['name' => 'create-paper', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Paper'],
            ['name' => 'update-paper', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Paper'],
            ['name' => 'delete-paper', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Paper'],

            ['name' => 'view-registrations', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Registration'],
            ['name' => 'view-registrations-by-user', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Registration'],
            ['name' => 'create-registration', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Registration'],
            ['name' => 'cancel-registration', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Registration'],
            ['name' => 'check-in-registration', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'Registration'],

            ['name' => 'reset-all-user-points', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'User'],
            ['name' => 'update-user-alert', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'User'],
            ['name' => 'send-reputation-alerts', 'api_path' => '/graphql', 'method' => 'POST', 'module' => 'User'],

            ['name' => 'upload avatar', 'api_path' => '/v1/upload/avatar', 'method' => 'POST', 'module' => 'Upload'],
            ['name' => 'upload speaker avatar', 'api_path' => '/v1/upload/speaker-avatar', 'method' => 'POST', 'module' => 'Upload'],
            ['name' => 'upload event image', 'api_path' => '/v1/upload/event-image', 'method' => 'POST', 'module' => 'Upload'],
            ['name' => 'upload paper file', 'api_path' => '/v1/upload/pages', 'method' => 'POST', 'module' => 'Upload'],
            ['name' => 'download paper', 'api_path' => '/v1/download/paper/{paperId}', 'method' => 'GET', 'module' => 'Upload'],
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
