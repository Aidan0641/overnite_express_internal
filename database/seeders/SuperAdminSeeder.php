<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        // Create Super Admin
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('admin1234'),
            'role' => 'superadmin',
        ]);

        $superadmin->tokens()->delete(); // Clear existing tokens
        $token = $superadmin->createToken('superadmin-token')->plainTextToken;

        echo "Superadmin Token: " . $token . "\n";

        // Additional Users
        $users = [
            ['name' => 'Overnite', 'role' => 'admin'],
            ['name' => 'Codligence', 'role' => 'user'],
            ['name' => 'Salted Peanuts', 'role' => 'user'],
            ['name' => 'Eclipse Plus', 'role' => 'user'],
            ["name" => "Julie's", 'role' => 'user']
        ];

        foreach ($users as $user) {
            User::create([
                'name' => $user['name'],
                'email' => strtolower(str_replace(' ', '', str_replace("'", '', $user['name']))) . '@example.com',
                'password' => bcrypt('123456'),
                'role' => $user['role'],
            ]);
        }
    }
}
