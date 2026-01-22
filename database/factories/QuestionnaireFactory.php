<?php

namespace Liangjin0228\Questionnaire\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Liangjin0228\Questionnaire\Enums\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Models\Questionnaire;

class QuestionnaireFactory extends Factory
{
    protected $model = Questionnaire::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'status' => QuestionnaireStatus::PUBLISHED->value,
            'slug' => $this->faker->slug(),
            'settings' => [],
            'user_id' => 1, // Default user ID, typically overridden or created in tests
        ];
    }
}
