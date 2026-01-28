<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Shared\Exception;

use DomainException as PhpDomainException;

abstract class DomainException extends PhpDomainException
{
    public static function with(string $message, int $code = 0): static
    {
        return new static($message, $code);
    }
}
