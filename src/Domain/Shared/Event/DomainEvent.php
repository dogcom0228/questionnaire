<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Shared\Event;

use Carbon\CarbonImmutable;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

abstract class DomainEvent extends ShouldBeStored
{
    protected CarbonImmutable $occurredAt;

    public function __construct()
    {
        $this->occurredAt = CarbonImmutable::now();
    }

    public function setOccurredAt(CarbonImmutable $occurredAt): self
    {
        $this->occurredAt = $occurredAt;

        return $this;
    }

    public function occurredAt(): CarbonImmutable
    {
        return $this->occurredAt;
    }

    public function createdAt(): CarbonImmutable
    {
        return $this->occurredAt;
    }

    abstract public function toArray(): array;
}
