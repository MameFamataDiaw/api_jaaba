<?php

namespace Database\Factories;

use App\Models\Adresse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Adresse>
 */
class AdresseFactory extends Factory
{
    protected $model = Adresse::class;

    public function definition(): array
    {
        return [
            'pays' => $this->faker->country(),
            'ville' => $this->faker->city(),
            'codePostal' => $this->faker->postcode(),
        ];
    }
}
