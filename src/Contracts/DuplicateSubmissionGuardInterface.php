<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts;

use Illuminate\Http\Request;
use Liangjin0228\Questionnaire\Models\Questionnaire;

interface DuplicateSubmissionGuardInterface
{
    /**
     * Check if the current request can submit to the questionnaire.
     */
    public function canSubmit(Questionnaire $questionnaire, Request $request): bool;

    /**
     * Get the reason why submission is not allowed.
     */
    public function getRejectionReason(Questionnaire $questionnaire, Request $request): ?string;

    /**
     * Mark the questionnaire as submitted for the current request.
     */
    public function markAsSubmitted(Questionnaire $questionnaire, Request $request): void;

    /**
     * Get the guard strategy identifier.
     */
    public function getIdentifier(): string;
}
