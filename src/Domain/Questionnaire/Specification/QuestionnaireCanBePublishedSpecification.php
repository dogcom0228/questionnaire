<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Specification;

use Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire;
use Liangjin0228\Questionnaire\Domain\Shared\Specification\Specification;

final class QuestionnaireCanBePublishedSpecification extends Specification
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (! $candidate instanceof Questionnaire) {
            return false;
        }

        $hasQuestions = new QuestionnaireHasQuestionsSpecification;
        $isDraft = $candidate->status()->isDraft();

        return $isDraft && $hasQuestions->isSatisfiedBy($candidate);
    }
}
