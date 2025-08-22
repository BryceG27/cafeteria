<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Frutta', 'description' => 'Categoria di frutta fresca'],
            ['name' => 'Verdura', 'description' => 'Categoria di verdura fresca'],
            ['name' => 'Latticini', 'description' => 'Categoria di prodotti lattiero-caseari'],
            ['name' => 'Carne', 'description' => 'Categoria di carne fresca e lavorata'],
            ['name' => 'Pesce', 'description' => 'Categoria di pesce fresco e frutti di mare'],
            ['name' => 'Pane e prodotti da forno', 'description' => 'Categoria di pane, dolci e prodotti da forno'],
            ['name' => 'Bevande', 'description' => 'Categoria di bevande analcoliche e alcoliche'],
            ['name' => 'Snack e dolci', 'description' => 'Categoria di snack, dolci e cioccolato'],
            ['name' => 'Prodotti per la colazione', 'description' => 'Categoria di cereali, marmellate e altri prodotti per la colazione'],
            ['name' => 'Alimenti confezionati', 'description' => 'Categoria di alimenti confezionati e pronti da mangiare']
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create($category);
        }
    }
}
