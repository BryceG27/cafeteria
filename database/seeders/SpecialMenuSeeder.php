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
        $menus = [
            [
                'name' => 'Hamburger',
                'description' => 'Il menù è compreso di patatine, bibita a scelta e hamburger',
                'img_url' => public_path('/assets/media/various/menus/hamburger menu.png'),
                'product_id' => 102,
                'price' => 0.0
            ],
            [
                'name' => 'Hamburger di pollo',
                'description' => 'Il menù è compreso di patatine, bibita a scelta e hamburger di pollo',
                'img_url' => public_path('/assets/media/various/menus/chicken menu.png'),
                'product_id' => 39,
                'price' => 0.0
            ],
            [
                'name' => 'Wrap con pollo',
                'description' => 'Il menù è compreso di patatine, bibita a scelta e wrap con pollo',
                'img_url' => public_path('/assets/media/various/menus/wrap menu.png'),
                'product_id' => 39,
                'price' => 0.0
            ],
        ];

        foreach ($menus as $menu) {
            \App\Models\SpecialMenu::create($menu);
        }

    }
}
