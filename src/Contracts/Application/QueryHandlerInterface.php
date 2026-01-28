<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Application;

interface QueryHandlerInterface
{
    public function handle(QueryInterface $query): mixed;
}
