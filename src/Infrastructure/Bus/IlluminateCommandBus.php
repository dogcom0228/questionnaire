<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Bus;

use Illuminate\Contracts\Container\Container;
use Liangjin0228\Questionnaire\Contracts\Application\Bus\CommandBusInterface;
use Liangjin0228\Questionnaire\Contracts\Application\CommandInterface;

final class IlluminateCommandBus implements CommandBusInterface
{
    /** @var array<class-string<CommandInterface>, class-string> */
    private array $handlers = [];

    public function __construct(
        private readonly Container $container
    ) {}

    public function register(string $commandClass, string $handlerClass): void
    {
        $this->handlers[$commandClass] = $handlerClass;
    }

    public function dispatch(CommandInterface $command): mixed
    {
        $handlerClass = $this->resolveHandler($command);
        $handler = $this->container->make($handlerClass);

        return $handler->handle($command);
    }

    private function resolveHandler(CommandInterface $command): string
    {
        $commandClass = get_class($command);

        // 先檢查已註冊的處理器
        if (isset($this->handlers[$commandClass])) {
            return $this->handlers[$commandClass];
        }

        // 約定優於配置：將 Command 替換為 Handler
        $handlerClass = preg_replace('/Command$/', 'Handler', $commandClass);

        if ($handlerClass && class_exists($handlerClass)) {
            return $handlerClass;
        }

        throw new \RuntimeException(
            sprintf('No handler found for command: %s', $commandClass)
        );
    }
}
