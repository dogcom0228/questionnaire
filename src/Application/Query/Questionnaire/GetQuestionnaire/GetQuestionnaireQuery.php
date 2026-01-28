<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Query\Questionnaire\GetQuestionnaire;

use Liangjin0228\Questionnaire\Contracts\Application\QueryInterface;

final readonly class GetQuestionnaireQuery implements QueryInterface
{
    public function __construct(
        public string $id,
        public bool $includeQuestions = true
    ) {}
}
