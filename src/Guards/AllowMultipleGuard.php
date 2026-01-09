<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Guards;

use Illuminate\Http\Request;
use Liangjin0228\Questionnaire\Contracts\DuplicateSubmissionGuardInterface;
use Liangjin0228\Questionnaire\Models\Questionnaire;

/**
 * Allows multiple submissions from the same source.
 */
class AllowMultipleGuard implements DuplicateSubmissionGuardInterface
{
    /**
     * {@inheritdoc}
     */
    public function canSubmit(Questionnaire $questionnaire, Request $request): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getRejectionReason(Questionnaire $questionnaire, Request $request): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function markAsSubmitted(Questionnaire $questionnaire, Request $request): void
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
