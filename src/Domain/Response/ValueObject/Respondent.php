<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Response\ValueObject;

use Liangjin0228\Questionnaire\Domain\Response\Exception\InvalidRespondentException;
use Liangjin0228\Questionnaire\Domain\Shared\ValueObject\ValueObject;

final class Respondent extends ValueObject
{
    private function __construct(
        private readonly ?string $type,
        private readonly ?string $id
    ) {
        $this->validate();
    }

    public static function anonymous(): self
    {
        return new self(null, null);
    }

    public static function authenticated(string $type, string $id): self
    {
        return new self($type, $id);
    }

    private function validate(): void
    {
        if (($this->type === null && $this->id !== null) ||
            ($this->type !== null && $this->id === null)) {
            throw InvalidRespondentException::inconsistentState();
        }
    }

    public function isAnonymous(): bool
    {
        return $this->type === null && $this->id === null;
    }

    public function isAuthenticated(): bool
    {
        return ! $this->isAnonymous();
    }

    public function type(): ?string
    {
        return $this->type;
    }

    public function id(): ?string
    {
        return $this->id;
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->type === $other->type
            && $this->id === $other->id;
    }

    public function value(): array
    {
        return [
            'type' => $this->type,
            'id' => $this->id,
        ];
    }

    public function __toString(): string
    {
        if ($this->isAnonymous()) {
            return 'anonymous';
        }

        return "{$this->type}:{$this->id}";
    }

    public function jsonSerialize(): array
    {
        return $this->value();
    }
}
