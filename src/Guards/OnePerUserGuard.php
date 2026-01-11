<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Guards;

use Liangjin0228\Questionnaire\Contracts\DuplicateSubmissionGuardInterface;
use Liangjin0228\Questionnaire\Contracts\ResponseRepositoryInterface;
use Liangjin0228\Questionnaire\DTOs\SubmitResponseData;
use Liangjin0228\Questionnaire\Models\Questionnaire;

/**
 * Allows only one submission per authenticated user.
 */
class OnePerUserGuard implements DuplicateSubmissionGuardInterface
{
    public function __construct(
        protected ResponseRepositoryInterface $responseRepository
    ) {}

    /**
     * {@inheritdoc}
     */
    public function canSubmit(Questionnaire $questionnaire, SubmitResponseData $data): bool
    {
        if ($data->userId === null) {
            // If user is not authenticated, fall back to allowing (or you could deny)
            return true;
        }

        $userModel = config('questionnaire.models.user') ?? config('auth.providers.users.model') ?? 'App\\Models\\User';

        return ! $this->responseRepository->hasRespondentSubmitted(
            $questionnaire,
            $userModel,
            $data->userId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRejectionReason(Questionnaire $questionnaire, SubmitResponseData $data): ?string
    {
        if (! $this->canSubmit($questionnaire, $data)) {
            return 'You have already submitted a response to this questionnaire.';
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function markAsSubmitted(Questionnaire $questionnaire, SubmitResponseData $data): void
    {
        // The submission is already recorded in the database via the response
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return 'one_per_user';
    }
}
