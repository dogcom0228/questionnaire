<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Services;

use Illuminate\Support\Facades\Validator;
use Liangjin0228\Questionnaire\Contracts\QuestionTypeRegistryInterface;
use Liangjin0228\Questionnaire\Contracts\ValidationStrategyInterface;
use Liangjin0228\Questionnaire\Models\Questionnaire;

class DefaultValidationStrategy implements ValidationStrategyInterface
{
    public function __construct(
        protected QuestionTypeRegistryInterface $questionTypeRegistry
    ) {}

    /**
     * {@inheritdoc}
     */
    public function validate(Questionnaire $questionnaire, array $data): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make(
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
            $key = "question_{$question->id}";
            $questionRules = [];

            // Required rule
            if ($question->required) {
                $questionRules[] = 'required';
            } else {
                $questionRules[] = 'nullable';
            }

            // Get type-specific rules
            $questionType = $this->questionTypeRegistry->get($question->type);
            if ($questionType) {
                $typeRules = $questionType->getValidationRules($question);
                $questionRules = array_merge($questionRules, $typeRules);
            }

            $rules[$key] = $questionRules;
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
            $key = "question_{$question->id}";
            $questionType = $this->questionTypeRegistry->get($question->type);

            if ($question->required) {
                $messages["{$key}.required"] = "Please answer: {$question->content}";
            }

            if ($questionType) {
                foreach ($questionType->getValidationMessages() as $rule => $message) {
                    $messages["{$key}.{$rule}"] = $message;
                }
            }
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
            $key = "question_{$question->id}";
            $attributes[$key] = $question->content;
        }

        return $attributes;
    }
}
