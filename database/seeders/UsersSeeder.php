<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'nama' => 'Administrator',
            'email' => 'admin@gmail.com',
            'telepon' => '081234567890',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'nama' => 'Checker',
            'email' => 'checker@gmail.com',
            'telepon' => '081234567891',
            'password' => Hash::make('password'),
            'role' => 'checker',
        ]);

        User::create([
            'nama' => 'Customer',
            'email' => 'customer@gmail.com',
            'telepon' => '081234567892',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);
    }
}
