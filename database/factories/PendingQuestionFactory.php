<?php

namespace Database\Factories;

use App\PendingQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

class PendingQuestionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PendingQuestion::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [];
    }
}
