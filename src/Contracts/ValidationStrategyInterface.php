<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts;

use Illuminate\Contracts\Validation\Validator;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;

interface ValidationStrategyInterface
{
    /**
     * Validate the submission data for a questionnaire.
     *
     * @param  array<string, mixed>  $data
     */
    public function validate(Questionnaire $questionnaire, array $data): Validator;

    /**
     * Get validation rules for a questionnaire.
     *
     * @return array<string, mixed>
     */
    public function getRules(Questionnaire $questionnaire): array;

    /**
     * Get validation messages for a questionnaire.
     *
     * @return array<string, string>
     */
    public function getMessages(Questionnaire $questionnaire): array;

    /**
     * Get validation attributes for a questionnaire.
     *
     * @return array<string, string>
     */
    public function getAttributes(Questionnaire $questionnaire): array;
}
