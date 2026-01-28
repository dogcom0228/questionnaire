<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Response\ValueObject;

use Liangjin0228\Questionnaire\Domain\Response\Exception\InvalidIpAddressException;
use Liangjin0228\Questionnaire\Domain\Shared\ValueObject\ValueObject;

final class IpAddress extends ValueObject
{
    private function __construct(
        private readonly string $address
    ) {
        $this->validate();
    }

    public static function fromString(string $address): self
    {
        return new self($address);
    }

    private function validate(): void
    {
        if (! filter_var($this->address, FILTER_VALIDATE_IP)) {
            throw InvalidIpAddressException::invalid($this->address);
        }
    }

    public function value(): string
    {
        return $this->address;
    }

    public function isIpv4(): bool
    {
        return filter_var($this->address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    public function isIpv6(): bool
    {
        return filter_var($this->address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->address === $other->address;
    }

    public function toString(): string
    {
        return $this->address;
    }

    public function __toString(): string
    {
        return $this->address;
    }

    public function jsonSerialize(): string
    {
        return $this->address;
    }
}
