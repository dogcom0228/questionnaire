<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Persistence\EventSourcedRepositories;

use Liangjin0228\Questionnaire\Contracts\EventSourcedResponseRepositoryInterface;
use Liangjin0228\Questionnaire\Domain\Response\Aggregate\Response;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\ResponseId;

final class EventSourcedResponseRepository implements EventSourcedResponseRepositoryInterface
{
    public function retrieve(ResponseId $id): Response
    {
        return Response::retrieve($id->toUuid());
    }

    public function persist(Response $response): void
    {
        $response->persist();
    }
}
