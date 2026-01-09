<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\QuestionTypes;

use Liangjin0228\Questionnaire\Models\Question;

class RadioQuestionType extends AbstractQuestionType
{
    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return 'radio';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Single Choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Select one option from a list.';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon(): string
    {
        return 'mdi-radiobox-marked';
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
            'inline' => false,
            'allow_other' => false,
        ];
    }
}
