<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Application\Bus;

use Liangjin0228\Questionnaire\Contracts\Application\QueryInterface;

interface QueryBusInterface
{
    public function dispatch(QueryInterface $query): mixed;
}
