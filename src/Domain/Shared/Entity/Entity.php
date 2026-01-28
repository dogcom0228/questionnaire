<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Shared\Entity;

use Liangjin0228\Questionnaire\Domain\Shared\ValueObject\EntityId;

abstract class Entity
{
    public function __construct(
        protected readonly EntityId $id
    ) {}

    public function id(): EntityId
    {
        return $this->id;
    }

    public function equals(Entity $other): bool
    {
        if (! $other instanceof static) {
            return false;
        }

        return $this->id->equals($other->id);
    }
}
