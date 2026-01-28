<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Exception;

use Liangjin0228\Questionnaire\Domain\Shared\Exception\DomainException;

final class InvalidQuestionnaireTitleException extends DomainException
{
    public static function tooShort(int $minLength): self
    {
        return new self("Questionnaire title must be at least {$minLength} characters long.");
    }

    public static function tooLong(int $maxLength): self
    {
        return new self("Questionnaire title must not exceed {$maxLength} characters.");
    }

    public static function empty(): self
    {
        return new self('Questionnaire title cannot be empty.');
    }
}
