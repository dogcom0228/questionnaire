<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Liangjin0228\Questionnaire\Models\Response;

class ResponseSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Response $response
    ) {}
}
