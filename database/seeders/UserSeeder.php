<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

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
    }
}
