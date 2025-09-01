<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Fabrizio',
                'surname' => 'Guarino',
                'email' => 'fabrizio1.guarino@outlook.it',
                'user_group_id' => 1,
                'password' => Hash::make('password')
            ],
            [
                'name' => 'Claudio',
                'surname' => 'Alimena',
                'email' => 'claudioalimena@gmail.com',
                'user_group_id' => 1,
                'password' => Hash::make('password')
            ]
        ];

        foreach ($users as $user) {
            \App\Models\User::create($user);
        }
    }
}
