<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'nom' => "Gueye",
            'prenom' => "Aminata",
            'telephone' => "771234567",
            'email' => "gueyeami@gmail.com",
            'role_id' => 2,
            'password' => "p@sser123",

        ]);
        DB::table('users')->insert([
            'nom' => "Sow",
            'prenom' => "Marieme",
            'telephone' => "770987654",
            'email' => "sowmari@gmail.com",
            'role_id' => 2,
            'password' => "p@sser123",
        ]);
    }
}
