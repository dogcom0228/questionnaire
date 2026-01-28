<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Response\Specification;

use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionId;
use Liangjin0228\Questionnaire\Domain\Response\Aggregate\Response;
use Liangjin0228\Questionnaire\Domain\Shared\Specification\Specification;

/**
 * @extends Specification<Response>
 */
final class ResponseHasAnswerForQuestionSpecification extends Specification
{
    public function __construct(
        private readonly QuestionId $questionId
    ) {}

    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (! $candidate instanceof Response) {
            return false;
        }

        return $candidate->hasAnswer($this->questionId);
    }
}
