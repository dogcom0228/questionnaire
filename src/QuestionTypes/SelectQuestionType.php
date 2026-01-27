<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\QuestionTypes;

use Liangjin0228\Questionnaire\Domain\Question\Models\Question;

class SelectQuestionType extends AbstractQuestionType
{
    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return 'select';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Dropdown';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'A dropdown select input.';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon(): string
    {
        return 'mdi-menu-down';
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
        $options = $question->options ?? [];
        $validValues = array_map(fn ($opt) => $opt['value'] ?? $opt, $options);

        return [
            'in:'.implode(',', $validValues),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationMessages(): array
    {
        return [
            'in' => 'Please select a valid option.',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function formatValue(mixed $value, Question $question): string
    {
        $options = $question->options ?? [];

        foreach ($options as $option) {
            $optValue = $option['value'] ?? $option;
            $optLabel = $option['label'] ?? $option;

            if ($optValue == $value) {
                return $optLabel;
            }
        }

        return (string) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        return [
            'searchable' => false,
            'clearable' => false,
        ];
    }
}
