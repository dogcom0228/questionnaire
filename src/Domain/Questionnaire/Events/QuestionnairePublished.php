<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;

class QuestionnairePublished
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Questionnaire $questionnaire
    ) {}
}
