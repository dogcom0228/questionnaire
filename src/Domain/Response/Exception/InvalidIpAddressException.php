<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Response\Exception;

use Liangjin0228\Questionnaire\Domain\Shared\Exception\DomainException;

final class InvalidIpAddressException extends DomainException
{
    public static function invalid(string $address): self
    {
        return new self(sprintf('Invalid IP address: "%s"', $address));
    }
}
