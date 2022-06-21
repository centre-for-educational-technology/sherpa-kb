<?php

namespace Database\Factories;

use App\Answer;
use App\States\Answer\Published;
use App\States\Answer\Translated;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnswerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Answer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [];
    }

    /**
     * Set answer status as translated.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function translated()
    {
        return $this->state(function () {
            return [
                'status' => Translated::$name,
            ];
        });
    }

    /**
     * Set answer status as published.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function published()
    {
        return $this->state(function () {
            return [
                'status' => Published::$name,
            ];
        });
    }
}
