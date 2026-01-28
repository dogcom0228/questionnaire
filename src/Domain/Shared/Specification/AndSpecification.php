<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Shared\Specification;

final class AndSpecification extends Specification
{
    public function __construct(
        private readonly Specification $left,
        private readonly Specification $right
    ) {}

    public function isSatisfiedBy(mixed $candidate): bool
    {
        return $this->left->isSatisfiedBy($candidate)
            && $this->right->isSatisfiedBy($candidate);
    }
}
