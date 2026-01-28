<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Application\Bus;

use Liangjin0228\Questionnaire\Domain\Shared\Event\DomainEvent;

interface EventBusInterface
{
    public function dispatch(DomainEvent $event): void;

    /**
     * @param  array<DomainEvent>  $events
     */
    public function dispatchMany(array $events): void;
}
