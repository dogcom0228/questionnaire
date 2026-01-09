<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\QuestionTypes;

use Liangjin0228\Questionnaire\Models\Question;

class CheckboxQuestionType extends AbstractQuestionType
{
    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return 'checkbox';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Multiple Choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Select multiple options from a list.';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon(): string
    {
        return 'mdi-checkbox-marked';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsOptions(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationRules(Question $question): array
    {
        $rules = ['array'];

        $settings = $question->settings ?? [];

        if (isset($settings['min_selections'])) {
            $rules[] = 'min:'.$settings['min_selections'];
        }

        if (isset($settings['max_selections'])) {
            $rules[] = 'max:'.$settings['max_selections'];
        }

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationMessages(): array
    {
        return [
            'array' => 'Invalid selection format.',
            'min' => 'Please select at least :min options.',
            'max' => 'Please select no more than :max options.',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function transformValue(mixed $value): mixed
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return is_array($value) ? $value : [$value];
    }

    /**
     * {@inheritdoc}
     */
    public function formatValue(mixed $value, Question $question): string
    {
        $values = is_array($value) ? $value : json_decode((string) $value, true) ?? [$value];
        $options = $question->options ?? [];

        $labels = [];
        foreach ($values as $val) {
            foreach ($options as $option) {
                $optValue = $option['value'] ?? $option;
                $optLabel = $option['label'] ?? $option;

                if ($optValue == $val) {
                    $labels[] = $optLabel;
                    break;
                }
            }
        }

        return implode(', ', $labels);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        return [
            'inline' => false,
            'min_selections' => null,
            'max_selections' => null,
        ];
    }
}
