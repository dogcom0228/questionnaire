<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Response\ValueObject;

use Liangjin0228\Questionnaire\Domain\Shared\ValueObject\ValueObject;

final class UserAgent extends ValueObject
{
    private function __construct(
        private readonly string $userAgent
    ) {}

    public static function fromString(string $userAgent): self
    {
        return new self($userAgent);
    }

    public function value(): string
    {
        return $this->userAgent;
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->userAgent === $other->userAgent;
    }

    public function toString(): string
    {
        return $this->userAgent;
    }

    public function __toString(): string
    {
        return $this->userAgent;
    }

    public function jsonSerialize(): string
    {
        return $this->userAgent;
    }
}
