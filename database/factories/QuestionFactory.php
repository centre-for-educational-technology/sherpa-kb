<?php

namespace Database\Factories;

use App\Question;
use App\States\Question\Published;
use App\States\Question\Translated;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Question::class;

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
     * Set question status as translated.
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
     * Set question status as published.
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
