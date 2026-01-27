<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\QuestionTypes;

use Liangjin0228\Questionnaire\Domain\Question\Models\Question;

class TextQuestionType extends AbstractQuestionType
{
    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Short Text';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'A single line text input for short answers.';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon(): string
    {
        return 'mdi-form-textbox';
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
            $rules[] = 'max:255';
        }

        if (isset($settings['regex'])) {
            $rules[] = 'regex:'.$settings['regex'];
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
            'max_length' => 255,
        ];
    }
}
