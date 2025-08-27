<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Colazione'
            ],
            [
                'name' => 'Primo'
            ],
            [
                'name' => 'Secondo'
            ],
            [
                'name' => 'Contorno'
            ],
        ];

        foreach ($types as $type) {
            \App\Models\ProductType::create($type);
        }
    }
}
