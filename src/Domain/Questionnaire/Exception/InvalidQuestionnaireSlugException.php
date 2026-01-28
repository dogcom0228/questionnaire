<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Exception;

use Liangjin0228\Questionnaire\Domain\Shared\Exception\DomainException;

final class InvalidQuestionnaireSlugException extends DomainException
{
    public static function tooShort(int $minLength): self
    {
        return new self("Questionnaire slug must be at least {$minLength} characters long.");
    }

    public static function tooLong(int $maxLength): self
    {
        return new self("Questionnaire slug must not exceed {$maxLength} characters.");
    }

    public static function invalidFormat(): self
    {
        return new self('Questionnaire slug must contain only lowercase letters, numbers, and hyphens.');
    }
}
