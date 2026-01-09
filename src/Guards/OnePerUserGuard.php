<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Guards;

use Illuminate\Http\Request;
use Liangjin0228\Questionnaire\Contracts\DuplicateSubmissionGuardInterface;
use Liangjin0228\Questionnaire\Contracts\ResponseRepositoryInterface;
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
    public function canSubmit(Questionnaire $questionnaire, Request $request): bool
    {
        $user = $request->user();

        if (!$user) {
            // If user is not authenticated, fall back to allowing (or you could deny)
            return true;
        }

        return !$this->responseRepository->hasRespondentSubmitted(
            $questionnaire,
            get_class($user),
            $user->getKey()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRejectionReason(Questionnaire $questionnaire, Request $request): ?string
    {
        if (!$this->canSubmit($questionnaire, $request)) {
            return 'You have already submitted a response to this questionnaire.';
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function markAsSubmitted(Questionnaire $questionnaire, Request $request): void
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
