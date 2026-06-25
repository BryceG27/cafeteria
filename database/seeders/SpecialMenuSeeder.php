<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SpecialMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\SpecialMenu::create([
            'name' => 'Pasta al forno',
            'description' => 'Menù completamente a caso',
            'product_id' => 14,
            'price' => 12.00,
        ]);
    }
}
