<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            'nomCategorie' => "Alimentaire",

        ]);
        DB::table('categories')->insert([
            'nomCategorie' => "Cosmétique",
        ]);
        DB::table('categories')->insert([
            'nomCategorie' => "Electronique",
        ]);
    }
}
