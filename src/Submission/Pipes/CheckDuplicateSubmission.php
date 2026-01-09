<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Submission\Pipes;

use Closure;
use Liangjin0228\Questionnaire\Exceptions\DuplicateSubmissionException;
use Liangjin0228\Questionnaire\Guards\DuplicateSubmissionGuardFactory;
use Liangjin0228\Questionnaire\Submission\SubmissionPassable;

class CheckDuplicateSubmission
{
    public function __construct(
        protected DuplicateSubmissionGuardFactory $duplicateGuardFactory
    ) {}

    public function handle(SubmissionPassable $passable, Closure $next)
    {
        $duplicateGuard = $this->duplicateGuardFactory->resolve($passable->questionnaire);

        if (!$duplicateGuard->canSubmit($passable->questionnaire, $passable->request)) {
            throw new DuplicateSubmissionException(
                $duplicateGuard->getRejectionReason($passable->questionnaire, $passable->request)
                    ?? 'You have already submitted a response to this questionnaire.'
            );
        }

        $response = $next($passable);

        // Post-processing: Mark as submitted
        // NOTE: This happens AFTER the response is successfully saved (because $next returns the result of the chain)
        // Check if response was created
        if ($passable->response) {
            $duplicateGuard->markAsSubmitted($passable->questionnaire, $passable->request);
        }

        return $response;
    }
}
