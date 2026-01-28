<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Query\Questionnaire\GetQuestionnaire;

use Liangjin0228\Questionnaire\Application\DTO\Output\QuestionnaireOutput;
use Liangjin0228\Questionnaire\Contracts\Application\QueryHandlerInterface;
use Liangjin0228\Questionnaire\Contracts\Application\QueryInterface;

final readonly class GetQuestionnaireHandler implements QueryHandlerInterface
{
    public function handle(QueryInterface $query): ?QuestionnaireOutput
    {
        throw new \RuntimeException('TODO: Implement after Phase 5.4 (Read Models) - Needs QuestionnaireModel and QuestionnaireMapper');
    }
}
