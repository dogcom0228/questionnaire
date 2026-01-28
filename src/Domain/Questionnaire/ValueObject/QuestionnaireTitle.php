<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject;

use Liangjin0228\Questionnaire\Domain\Questionnaire\Exception\InvalidQuestionnaireTitleException;
use Liangjin0228\Questionnaire\Domain\Shared\ValueObject\ValueObject;

final class QuestionnaireTitle extends ValueObject
{
    private const MIN_LENGTH = 3;

    private const MAX_LENGTH = 255;

    private function __construct(
        private readonly string $title
    ) {
        $this->validate();
    }

    public static function fromString(string $title): self
    {
        return new self($title);
    }

    private function validate(): void
    {
        $length = mb_strlen($this->title);

        if ($length < self::MIN_LENGTH) {
            throw InvalidQuestionnaireTitleException::tooShort(self::MIN_LENGTH);
        }

        if ($length > self::MAX_LENGTH) {
            throw InvalidQuestionnaireTitleException::tooLong(self::MAX_LENGTH);
        }

        if (trim($this->title) === '') {
            throw InvalidQuestionnaireTitleException::empty();
        }
    }

    public function equals(ValueObject $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        return $this->title === $other->title;
    }

    public function value(): string
    {
        return $this->title;
    }
}
