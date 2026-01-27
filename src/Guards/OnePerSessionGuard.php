<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Guards;

use Illuminate\Support\Facades\Session;
use Liangjin0228\Questionnaire\Contracts\DuplicateSubmissionGuardInterface;
use Liangjin0228\Questionnaire\DTOs\SubmitResponseData;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;

/**
 * Allows only one submission per session.
 */
class OnePerSessionGuard implements DuplicateSubmissionGuardInterface
{
    protected const SESSION_KEY_PREFIX = 'questionnaire_submitted_';

    /**
     * {@inheritdoc}
     */
    public function canSubmit(Questionnaire $questionnaire, SubmitResponseData $data): bool
    {
        $sessionKey = $this->getSessionKey($questionnaire);

        return ! Session::has($sessionKey);
    }

    /**
     * {@inheritdoc}
     */
    public function getRejectionReason(Questionnaire $questionnaire, SubmitResponseData $data): ?string
    {
        if (! $this->canSubmit($questionnaire, $data)) {
            return 'You have already submitted a response to this questionnaire in this session.';
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function markAsSubmitted(Questionnaire $questionnaire, SubmitResponseData $data): void
    {
        $sessionKey = $this->getSessionKey($questionnaire);
        Session::put($sessionKey, now()->toIso8601String());
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return 'one_per_session';
    }

    /**
     * Get the session key for tracking submission.
     */
    protected function getSessionKey(Questionnaire $questionnaire): string
    {
        return self::SESSION_KEY_PREFIX.$questionnaire->id;
    }
}
