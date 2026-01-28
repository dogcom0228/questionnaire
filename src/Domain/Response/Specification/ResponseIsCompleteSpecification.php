<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Response\Specification;

use Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire;
use Liangjin0228\Questionnaire\Domain\Response\Aggregate\Response;
use Liangjin0228\Questionnaire\Domain\Shared\Specification\Specification;

/**
 * Checks if a response has answers for all required questions in a questionnaire.
 *
 * @extends Specification<Response>
 */
final class ResponseIsCompleteSpecification extends Specification
{
    public function __construct(
        private readonly Questionnaire $questionnaire
    ) {}

    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (! $candidate instanceof Response) {
            return false;
        }

        if (! $candidate->questionnaireId()->equals($this->questionnaire->id())) {
            return false;
        }
        foreach ($this->questionnaire->questions() as $question) {
            if (! $candidate->hasAnswer($question->id())) {
                return false;
            }
        }

        return true;
    }
}
