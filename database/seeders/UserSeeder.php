<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Wira', 'email' => 'owner@bukukita.test',      'role' => 'owner'],
            ['name' => 'Doni', 'email' => 'supervisor@bukukita.test', 'role' => 'supervisor'],
            ['name' => 'Andi', 'email' => 'admin@bukukita.test',      'role' => 'admin'],
            ['name' => 'Caca', 'email' => 'kasir@bukukita.test',      'role' => 'kasir'],
        ];

        foreach ($users as $user) {
            $role = Role::where('name', $user['role'])->first();
            if ($role) {
                User::updateOrCreate(
                    ['email' => $user['email']],
                    [
                        'name' => $user['name'],
                        'password' => Hash::make('password'),
                        'role_id' => $role->id,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
