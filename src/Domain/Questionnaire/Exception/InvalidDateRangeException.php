<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Exception;

use Liangjin0228\Questionnaire\Domain\Shared\Exception\DomainException;

final class InvalidDateRangeException extends DomainException
{
    public static function startAfterEnd(): self
    {
        return new self('Start date must be before or equal to end date.');
    }
}
