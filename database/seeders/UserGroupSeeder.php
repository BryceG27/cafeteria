<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user_groups = [
            [
                'name' => 'SuperAdmin',
                'description' => 'Super admin of the app. One and only'
            ],
            [
                'name' => 'Admin',
                'description' => 'Normal administrator'
            ],
            [
                'name' => 'Cliente',
                'description' => ''
            ]
        ];

        foreach ($user_groups as $group) {
            \App\Models\UserGroup::create($group);
        }
    }
}
