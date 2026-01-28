<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject;

use Carbon\CarbonImmutable;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Exception\InvalidDateRangeException;
use Liangjin0228\Questionnaire\Domain\Shared\ValueObject\ValueObject;

final class DateRange extends ValueObject
{
    private function __construct(
        private readonly ?CarbonImmutable $startDate,
        private readonly ?CarbonImmutable $endDate
    ) {
        $this->validate();
    }

    public static function create(?CarbonImmutable $startDate, ?CarbonImmutable $endDate): self
    {
        return new self($startDate, $endDate);
    }

    public static function unlimited(): self
    {
        return new self(null, null);
    }

    public static function from(CarbonImmutable $startDate): self
    {
        return new self($startDate, null);
    }

    public static function until(CarbonImmutable $endDate): self
    {
        return new self(null, $endDate);
    }

    private function validate(): void
    {
        if ($this->startDate && $this->endDate && $this->startDate->isAfter($this->endDate)) {
            throw InvalidDateRangeException::startAfterEnd();
        }
    }

    public function isActive(?CarbonImmutable $at = null): bool
    {
        $now = $at ?? CarbonImmutable::now();

        if ($this->startDate && $now->isBefore($this->startDate)) {
            return false;
        }

        if ($this->endDate && $now->isAfter($this->endDate)) {
            return false;
        }

        return true;
    }

    public function startDate(): ?CarbonImmutable
    {
        return $this->startDate;
    }

    public function endDate(): ?CarbonImmutable
    {
        return $this->endDate;
    }

    public function startsAt(): ?CarbonImmutable
    {
        return $this->startDate;
    }

    public function endsAt(): ?CarbonImmutable
    {
        return $this->endDate;
    }

    public function equals(ValueObject $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        $startEquals = ($this->startDate === null && $other->startDate === null)
            || ($this->startDate !== null && $other->startDate !== null && $this->startDate->equalTo($other->startDate));
        $endEquals = ($this->endDate === null && $other->endDate === null)
            || ($this->endDate !== null && $other->endDate !== null && $this->endDate->equalTo($other->endDate));

        return $startEquals && $endEquals;
    }

    /**
     * @return array<string, string|null>
     */
    public function value(): array
    {
        return [
            'start_date' => $this->startDate?->toIso8601String(),
            'end_date' => $this->endDate?->toIso8601String(),
        ];
    }
}
