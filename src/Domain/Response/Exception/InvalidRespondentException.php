<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Response\Exception;

use Liangjin0228\Questionnaire\Domain\Shared\Exception\DomainException;

final class InvalidRespondentException extends DomainException
{
    public static function inconsistentState(): self
    {
        return new self('Respondent type and ID must both be null or both be non-null.');
    }
}
