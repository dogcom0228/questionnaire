<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Submission\Pipes;

use Closure;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Enums\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Exceptions\QuestionnaireClosedException;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Submission\SubmissionPassable;

class EnsureQuestionnaireIsOpen
{
    public function handle(SubmissionPassable $passable, Closure $next)
    {
        $this->validateStatus($passable->questionnaire);

        return $next($passable);
    }

    protected function validateStatus(Questionnaire $questionnaire): void
    {
        if ($questionnaire->status !== QuestionnaireStatus::PUBLISHED->value) {
            throw new QuestionnaireClosedException(
                'This questionnaire is not currently accepting responses.'
            );
        }

        if ($questionnaire->starts_at && $questionnaire->starts_at->isFuture()) {
            throw new QuestionnaireClosedException(
                'This questionnaire has not started yet.'
            );
        }

        if ($questionnaire->ends_at && $questionnaire->ends_at->isPast()) {
            throw new QuestionnaireClosedException(
                'This questionnaire has ended.'
            );
        }

        if ($questionnaire->submission_limit !== null) {
            $responseCount = $questionnaire->responses()->count();
            if ($responseCount >= $questionnaire->submission_limit) {
                throw new QuestionnaireClosedException(
                    'This questionnaire has reached its submission limit.'
                );
            }
        }
    }
}
