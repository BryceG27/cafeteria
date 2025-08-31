<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DevSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            UserGroupSeeder::class,
            UserSeeder::class,
            PaymentMethodSeeder::class,
            CategorySeeder::class,
            ProductTypeSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
