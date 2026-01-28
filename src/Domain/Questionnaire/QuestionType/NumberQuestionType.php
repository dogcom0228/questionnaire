<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\QuestionType;

use Liangjin0228\Questionnaire\Domain\Question\Models\Question;

class NumberQuestionType extends AbstractQuestionType
{
    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return 'number';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Number';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'A numeric input field.';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon(): string
    {
        return 'mdi-numeric';
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationRules(Question $question): array
    {
        $rules = ['numeric'];

        $settings = $question->settings ?? [];

        if (isset($settings['min'])) {
            $rules[] = 'min:'.$settings['min'];
        }

        if (isset($settings['max'])) {
            $rules[] = 'max:'.$settings['max'];
        }

        if (isset($settings['integer']) && $settings['integer']) {
            $rules[] = 'integer';
        }

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationMessages(): array
    {
        return [
            'numeric' => 'Please enter a valid number.',
            'integer' => 'Please enter a whole number.',
            'min' => 'The number must be at least :min.',
            'max' => 'The number must not exceed :max.',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function transformValue(mixed $value): mixed
    {
        if ($value === '' || $value === null) {
            return null;
        }

        return is_numeric($value) ? $value + 0 : $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        return [
            'min' => null,
            'max' => null,
            'step' => 1,
            'integer' => false,
        ];
    }
}
