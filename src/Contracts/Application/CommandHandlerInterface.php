<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Application;

interface CommandHandlerInterface
{
    public function handle(CommandInterface $command): mixed;
}
