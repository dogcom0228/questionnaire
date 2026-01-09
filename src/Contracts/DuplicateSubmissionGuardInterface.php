<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts;

use Illuminate\Http\Request;
use Liangjin0228\Questionnaire\Models\Questionnaire;

interface DuplicateSubmissionGuardInterface
{
    /**
     * Check if the current request can submit to the questionnaire.
     *
     * @param Questionnaire $questionnaire
     * @param Request $request
     * @return bool
     */
    public function canSubmit(Questionnaire $questionnaire, Request $request): bool;

    /**
     * Get the reason why submission is not allowed.
     *
     * @param Questionnaire $questionnaire
     * @param Request $request
     * @return string|null
     */
    public function getRejectionReason(Questionnaire $questionnaire, Request $request): ?string;

    /**
     * Mark the questionnaire as submitted for the current request.
     *
     * @param Questionnaire $questionnaire
     * @param Request $request
     * @return void
     */
    public function markAsSubmitted(Questionnaire $questionnaire, Request $request): void;

    /**
     * Get the guard strategy identifier.
     *
     * @return string
     */
    public function getIdentifier(): string;
}
