<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AdFactory extends Factory
{
    protected $model = \App\Models\Ad::class;

    public function definition()
    {
        return [
            'image_url' => $this->faker->imageUrl(),
            'ads_type' => $this->faker->word(),
            'ads_product_id' => $this->faker->uuid(),
        ];
    }
}
