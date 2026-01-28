<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Shared\Specification;

final class NotSpecification extends Specification
{
    public function __construct(
        private readonly Specification $specification
    ) {}

    public function isSatisfiedBy(mixed $candidate): bool
    {
        return ! $this->specification->isSatisfiedBy($candidate);
    }
}
