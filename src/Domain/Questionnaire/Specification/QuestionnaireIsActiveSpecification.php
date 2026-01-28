<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Specification;

use Carbon\CarbonImmutable;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire;
use Liangjin0228\Questionnaire\Domain\Shared\Specification\Specification;

final class QuestionnaireIsActiveSpecification extends Specification
{
    public function __construct(
        private readonly ?CarbonImmutable $at = null
    ) {}

    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (! $candidate instanceof Questionnaire) {
            return false;
        }

        return $candidate->isActive($this->at);
    }
}
