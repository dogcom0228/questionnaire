<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Shared\ValueObject;

use JsonSerializable;
use Stringable;

abstract class ValueObject implements JsonSerializable, Stringable
{
    abstract public function equals(self $other): bool;

    abstract public function value(): mixed;

    public function __toString(): string
    {
        $value = $this->value();

        if (is_scalar($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            return json_encode($value, JSON_THROW_ON_ERROR);
        }

        return '';
    }

    public function jsonSerialize(): mixed
    {
        return $this->value();
    }
}
