<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Projector;

use Liangjin0228\Questionnaire\Contracts\Infrastructure\ProjectorInterface;
use Liangjin0228\Questionnaire\Domain\Response\Event\ResponseSubmitted;

final class ResponseProjector implements ProjectorInterface
{
    public function onResponseSubmitted(ResponseSubmitted $event): void
    {
        throw new \RuntimeException('TODO: Implement after Phase 5.4 (Read Models) - Needs ResponseModel and AnswerModel');
    }

    /**
     * @return array<class-string, string>
     */
    public function getSubscribedEvents(): array
    {
        return [
            ResponseSubmitted::class => 'onResponseSubmitted',
        ];
    }

    public function reset(): void
    {
        throw new \RuntimeException('TODO: Implement after Phase 5.4 (Read Models) - Needs AnswerModel and ResponseModel');
    }
}
