<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Shared\Aggregate;

use Ramsey\Uuid\UuidInterface;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot as SpatieAggregateRoot;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

abstract class AggregateRoot extends SpatieAggregateRoot
{
    protected int $aggregateVersion = 0;

    abstract public function getAggregateRootId(): UuidInterface;

    public function recordThat(ShouldBeStored $event): static
    {
        $this->recordedEvents[] = $event;
        $this->apply($event);

        return $this;
    }

    /**
     * Get all recorded events
     *
     * @return array<ShouldBeStored>
     */
    public function recordedEvents(): array
    {
        return $this->recordedEvents;
    }

    protected function apply(ShouldBeStored $event): void
    {
        $this->aggregateVersion++;

        $method = $this->getApplyMethodName($event);

        if (method_exists($this, $method)) {
            $this->$method($event);
        }
    }

    private function getApplyMethodName(ShouldBeStored $event): string
    {
        $classPath = get_class($event);
        $className = class_basename($classPath);

        return 'apply'.$className;
    }

    public function getAggregateVersion(): int
    {
        return $this->aggregateVersion;
    }
}
