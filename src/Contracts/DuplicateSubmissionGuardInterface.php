<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts;

use Liangjin0228\Questionnaire\DTOs\SubmitResponseData;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;

interface DuplicateSubmissionGuardInterface
{
    /**
     * Check if the current request can submit to the questionnaire.
     */
    public function canSubmit(Questionnaire $questionnaire, SubmitResponseData $data): bool;

    /**
     * Get the reason why submission is not allowed.
     */
    public function getRejectionReason(Questionnaire $questionnaire, SubmitResponseData $data): ?string;

    /**
     * Mark the questionnaire as submitted for the current request.
     */
    public function markAsSubmitted(Questionnaire $questionnaire, SubmitResponseData $data): void;

    /**
     * Get the guard strategy identifier.
     */
    public function getIdentifier(): string;
}
