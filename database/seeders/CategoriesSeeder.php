<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    public $categories = [
        'Elettronica',
        'Abbigliamento',
        'Salute e Bellezza',
        'Casa e Giardinaggio',
        'Giocattoli',
        'Sport',
        'Animali Domestici',
        'Libri e Riviste',
        'Accessori',
        'Motori',
    ];

    public function run(): void
    {
        foreach ($this->categories as $category) {
            Category::firstOrCreate([
                'name' => $category,
            ]);
        }
    }
}
