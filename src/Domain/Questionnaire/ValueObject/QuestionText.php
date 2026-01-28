<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject;

use Liangjin0228\Questionnaire\Domain\Questionnaire\Exception\InvalidQuestionTextException;
use Liangjin0228\Questionnaire\Domain\Shared\ValueObject\ValueObject;

final class QuestionText extends ValueObject
{
    private const MIN_LENGTH = 1;

    private const MAX_LENGTH = 1000;

    private function __construct(
        private readonly string $text
    ) {
        $this->validate();
    }

    public static function fromString(string $text): self
    {
        return new self($text);
    }

    private function validate(): void
    {
        $trimmedText = trim($this->text);

        if (empty($trimmedText)) {
            throw InvalidQuestionTextException::empty();
        }

        $length = mb_strlen($trimmedText);

        // MIN_LENGTH is currently 1, but this check remains for configurability
        if (self::MIN_LENGTH > 1 && $length < self::MIN_LENGTH) {
            throw InvalidQuestionTextException::tooShort($length, self::MIN_LENGTH);
        }

        if ($length > self::MAX_LENGTH) {
            throw InvalidQuestionTextException::tooLong($length, self::MAX_LENGTH);
        }
    }

    public function value(): string
    {
        return trim($this->text);
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->value() === $other->value();
    }

    public function __toString(): string
    {
        return $this->value();
    }

    public function jsonSerialize(): string
    {
        return $this->value();
    }
}
