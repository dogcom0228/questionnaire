<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Infrastructure;

interface ProjectorInterface
{
    /**
     * @return array<class-string, string>
     */
    public function getSubscribedEvents(): array;

    public function reset(): void;
}
