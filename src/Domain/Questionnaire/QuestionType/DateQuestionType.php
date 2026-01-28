<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\QuestionType;

use Liangjin0228\Questionnaire\Domain\Question\Models\Question;

class DateQuestionType extends AbstractQuestionType
{
    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return 'date';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Date';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'A date picker input.';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon(): string
    {
        return 'mdi-calendar';
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationRules(Question $question): array
    {
        $rules = ['date'];

        $settings = $question->settings ?? [];

        if (isset($settings['min_date'])) {
            $rules[] = 'after_or_equal:'.$settings['min_date'];
        }

        if (isset($settings['max_date'])) {
            $rules[] = 'before_or_equal:'.$settings['max_date'];
        }

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationMessages(): array
    {
        return [
            'date' => 'Please enter a valid date.',
            'after_or_equal' => 'The date must be on or after :date.',
            'before_or_equal' => 'The date must be on or before :date.',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function formatValue(mixed $value, Question $question): string
    {
        if (empty($value)) {
            return '';
        }

        try {
            $date = \Carbon\Carbon::parse($value);

            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return (string) $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        return [
            'format' => 'YYYY-MM-DD',
            'min_date' => null,
            'max_date' => null,
        ];
    }
}
