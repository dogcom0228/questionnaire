<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Exception;

use Liangjin0228\Questionnaire\Domain\Shared\Exception\DomainException;

final class InvalidQuestionTextException extends DomainException
{
    public static function empty(): self
    {
        return new self('Question text cannot be empty.');
    }

    public static function tooShort(int $actualLength, int $minLength): self
    {
        return new self(
            sprintf(
                'Question text is too short. Minimum length is %d characters, got %d.',
                $minLength,
                $actualLength
            )
        );
    }

    public static function tooLong(int $actualLength, int $maxLength): self
    {
        return new self(
            sprintf(
                'Question text is too long. Maximum length is %d characters, got %d.',
                $maxLength,
                $actualLength
            )
        );
    }
}
