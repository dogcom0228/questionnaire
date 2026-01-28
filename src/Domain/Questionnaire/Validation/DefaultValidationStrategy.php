<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Validation;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Liangjin0228\Questionnaire\Contracts\ValidationStrategyInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;

/**
 * Default validation strategy for questionnaire submissions.
 *
 * Generates validation rules based on question types and requirements.
 */
class DefaultValidationStrategy implements ValidationStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate(Questionnaire $questionnaire, array $data): Validator
    {
        return ValidatorFacade::make(
            $data,
            $this->getRules($questionnaire),
            $this->getMessages($questionnaire),
            $this->getAttributes($questionnaire)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRules(Questionnaire $questionnaire): array
    {
        $rules = [];

        foreach ($questionnaire->questions as $question) {
            $questionRules = [];

            // Required validation
            if ($question->is_required) {
                $questionRules[] = 'required';
            } else {
                $questionRules[] = 'nullable';
            }

            // Type-specific validation
            switch ($question->type) {
                case 'text':
                case 'textarea':
                    $questionRules[] = 'string';
                    if (isset($question->settings['max_length'])) {
                        $questionRules[] = 'max:'.$question->settings['max_length'];
                    }
                    break;

                case 'number':
                    $questionRules[] = 'numeric';
                    if (isset($question->settings['min'])) {
                        $questionRules[] = 'min:'.$question->settings['min'];
                    }
                    if (isset($question->settings['max'])) {
                        $questionRules[] = 'max:'.$question->settings['max'];
                    }
                    break;

                case 'email':
                    $questionRules[] = 'email';
                    break;

                case 'url':
                    $questionRules[] = 'url';
                    break;

                case 'date':
                    $questionRules[] = 'date';
                    break;

                case 'select':
                case 'radio':
                    $questionRules[] = 'string';
                    if (! empty($question->options)) {
                        $questionRules[] = 'in:'.implode(',', $question->options);
                    }
                    break;

                case 'checkbox':
                    $questionRules[] = 'array';
                    if (! empty($question->options)) {
                        $rules["{$question->id}.*"] = 'in:'.implode(',', $question->options);
                    }
                    break;

                case 'rating':
                    $questionRules[] = 'integer';
                    $min = $question->settings['min'] ?? 1;
                    $max = $question->settings['max'] ?? 5;
                    $questionRules[] = "between:{$min},{$max}";
                    break;

                default:
                    // Generic validation for unknown types
                    break;
            }

            $rules[$question->id] = $questionRules;
        }

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages(Questionnaire $questionnaire): array
    {
        $messages = [];

        foreach ($questionnaire->questions as $question) {
            $questionText = $question->text;

            $messages["{$question->id}.required"] = "The {$questionText} field is required.";
            $messages["{$question->id}.email"] = "Please enter a valid email address for {$questionText}.";
            $messages["{$question->id}.url"] = "Please enter a valid URL for {$questionText}.";
            $messages["{$question->id}.numeric"] = "The {$questionText} must be a number.";
            $messages["{$question->id}.date"] = "Please enter a valid date for {$questionText}.";
            $messages["{$question->id}.in"] = "Please select a valid option for {$questionText}.";
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(Questionnaire $questionnaire): array
    {
        $attributes = [];

        foreach ($questionnaire->questions as $question) {
            $attributes[$question->id] = $question->text;
        }

        return $attributes;
    }
}
