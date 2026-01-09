<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\QuestionTypes;

use Liangjin0228\Questionnaire\Models\Question;

class TextareaQuestionType extends AbstractQuestionType
{
    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return 'textarea';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Long Text';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'A multi-line text input for long answers.';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon(): string
    {
        return 'mdi-text-box';
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationRules(Question $question): array
    {
        $rules = ['string'];

        $settings = $question->settings ?? [];

        if (isset($settings['min_length'])) {
            $rules[] = 'min:'.$settings['min_length'];
        }

        if (isset($settings['max_length'])) {
            $rules[] = 'max:'.$settings['max_length'];
        } else {
            $rules[] = 'max:65535';
        }

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationMessages(): array
    {
        return [
            'string' => 'The answer must be text.',
            'min' => 'The answer must be at least :min characters.',
            'max' => 'The answer must not exceed :max characters.',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function transformValue(mixed $value): mixed
    {
        return trim((string) $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        return [
            'placeholder' => 'Enter your answer...',
            'rows' => 4,
            'max_length' => 65535,
        ];
    }
}
