<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Events;

use Liangjin0228\Questionnaire\DTOs\QuestionnaireData;
use Illuminate\Foundation\Events\Dispatchable;

class QuestionnaireCreating
{
    use Dispatchable;

    public function __construct(
        public QuestionnaireData $data,
        public ?int $userId
    ) {}
}
