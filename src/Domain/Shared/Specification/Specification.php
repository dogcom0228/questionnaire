<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Shared\Specification;

abstract class Specification
{
    abstract public function isSatisfiedBy(mixed $candidate): bool;

    public function and(Specification $other): AndSpecification
    {
        return new AndSpecification($this, $other);
    }

    public function or(Specification $other): OrSpecification
    {
        return new OrSpecification($this, $other);
    }

    public function not(): NotSpecification
    {
        return new NotSpecification($this);
    }
}
