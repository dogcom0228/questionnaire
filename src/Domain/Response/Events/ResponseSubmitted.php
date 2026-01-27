<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Response\Events;

use Liangjin0228\Questionnaire\Domain\Response\Models\Response;

class ResponseSubmitted
{
    public function __construct(
        public Response $response
    ) {}
}
