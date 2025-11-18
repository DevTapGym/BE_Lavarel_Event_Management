<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('123456'),
                'avatar' => null,
                'phone' => null,
                'is_active' => true,
            ]
        );

        $admin->roles = ['ADMIN'];
        $admin->save();

        $this->command->info('Admin user created or already exists');

        // Tạo account Organizer
        $organizer = User::firstOrCreate(
            ['email' => 'organizer@gmail.com'],
            [
                'name' => 'Organizer',
                'password' => bcrypt('123456'),
                'avatar' => null,
                'phone' => null,
                'is_active' => true,
            ]
        );

        $organizer->roles = ['ORGANIZER'];
        $organizer->save();

        $this->command->info('Organizer user created or already exists');
    }
}
