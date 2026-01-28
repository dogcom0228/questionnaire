<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Application\Bus;

use Liangjin0228\Questionnaire\Contracts\Application\CommandInterface;

interface CommandBusInterface
{
    public function dispatch(CommandInterface $command): mixed;
}
