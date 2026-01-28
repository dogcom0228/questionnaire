<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Bus;

use Illuminate\Contracts\Container\Container;
use Liangjin0228\Questionnaire\Contracts\Application\Bus\QueryBusInterface;
use Liangjin0228\Questionnaire\Contracts\Application\QueryInterface;

final class IlluminateQueryBus implements QueryBusInterface
{
    /** @var array<class-string<QueryInterface>, class-string> */
    private array $handlers = [];

    public function __construct(
        private readonly Container $container
    ) {}

    public function register(string $queryClass, string $handlerClass): void
    {
        $this->handlers[$queryClass] = $handlerClass;
    }

    public function dispatch(QueryInterface $query): mixed
    {
        $handlerClass = $this->resolveHandler($query);
        $handler = $this->container->make($handlerClass);

        return $handler->handle($query);
    }

    private function resolveHandler(QueryInterface $query): string
    {
        $queryClass = get_class($query);

        if (isset($this->handlers[$queryClass])) {
            return $this->handlers[$queryClass];
        }

        // 約定：Query -> Handler
        $handlerClass = preg_replace('/Query$/', 'Handler', $queryClass);

        if ($handlerClass && class_exists($handlerClass)) {
            return $handlerClass;
        }

        throw new \RuntimeException(
            sprintf('No handler found for query: %s', $queryClass)
        );
    }
}
