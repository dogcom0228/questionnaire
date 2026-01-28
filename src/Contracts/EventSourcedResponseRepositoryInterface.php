<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts;

use Liangjin0228\Questionnaire\Domain\Response\Aggregate\Response;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\ResponseId;

interface EventSourcedResponseRepositoryInterface
{
    public function retrieve(ResponseId $id): Response;

    public function persist(Response $response): void;
}
