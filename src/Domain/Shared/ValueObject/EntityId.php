<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Shared\ValueObject;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

abstract class EntityId extends ValueObject
{
    public function __construct(
        protected readonly UuidInterface $uuid
    ) {}

    public static function generate(): static
    {
        return new static(Uuid::uuid4());
    }

    public static function fromString(string $uuid): static
    {
        return new static(Uuid::fromString($uuid));
    }

    public function equals(ValueObject $other): bool
    {
        if (! $other instanceof static) {
            return false;
        }

        return $this->uuid->equals($other->uuid);
    }

    public function value(): string
    {
        return $this->uuid->toString();
    }

    public function toUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function toString(): string
    {
        return $this->uuid->toString();
    }
}
