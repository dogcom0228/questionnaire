<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Bus;

use Illuminate\Contracts\Events\Dispatcher;
use Liangjin0228\Questionnaire\Contracts\Application\Bus\EventBusInterface;
use Liangjin0228\Questionnaire\Domain\Shared\Event\DomainEvent;

final class IlluminateEventBus implements EventBusInterface
{
    public function __construct(
        private readonly Dispatcher $dispatcher
    ) {}

    public function dispatch(DomainEvent $event): void
    {
        $this->dispatcher->dispatch($event);
    }

    public function dispatchMany(array $events): void
    {
        foreach ($events as $event) {
            $this->dispatch($event);
        }
    }
}
