<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Guards;

use Liangjin0228\Questionnaire\Contracts\DuplicateSubmissionGuardInterface;
use Liangjin0228\Questionnaire\DTOs\SubmitResponseData;
use Liangjin0228\Questionnaire\Models\Questionnaire;

/**
 * Allows multiple submissions from the same source.
 */
class AllowMultipleGuard implements DuplicateSubmissionGuardInterface
{
    /**
     * {@inheritdoc}
     */
    public function canSubmit(Questionnaire $questionnaire, SubmitResponseData $data): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getRejectionReason(Questionnaire $questionnaire, SubmitResponseData $data): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function markAsSubmitted(Questionnaire $questionnaire, SubmitResponseData $data): void
    {
        // No-op for allow multiple
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return 'allow_multiple';
    }
}
