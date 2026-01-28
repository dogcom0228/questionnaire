<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Response\ValueObject;

use Liangjin0228\Questionnaire\Domain\Shared\ValueObject\ValueObject;

final class AnswerValue extends ValueObject
{
    private function __construct(
        private readonly mixed $value
    ) {}

    public static function fromMixed(mixed $value): self
    {
        return new self($value);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function fromArray(array $value): self
    {
        return new self($value);
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public static function fromBool(bool $value): self
    {
        return new self($value);
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function toMixed(): mixed
    {
        return $this->value;
    }

    public function isArray(): bool
    {
        return is_array($this->value);
    }

    public function isString(): bool
    {
        return is_string($this->value);
    }

    public function isNumeric(): bool
    {
        return is_numeric($this->value);
    }

    public function isBool(): bool
    {
        return is_bool($this->value);
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }

    public function __toString(): string
    {
        if (is_array($this->value)) {
            return json_encode($this->value, JSON_THROW_ON_ERROR);
        }

        if (is_bool($this->value)) {
            return $this->value ? 'true' : 'false';
        }

        return (string) $this->value;
    }

    public function jsonSerialize(): mixed
    {
        return $this->value;
    }
}
