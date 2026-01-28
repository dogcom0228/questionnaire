<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Specification;

use Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire;
use Liangjin0228\Questionnaire\Domain\Shared\Specification\Specification;

final class QuestionnaireAcceptsModificationsSpecification extends Specification
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (! $candidate instanceof Questionnaire) {
            return false;
        }

        return $candidate->status()->isDraft();
    }
}
