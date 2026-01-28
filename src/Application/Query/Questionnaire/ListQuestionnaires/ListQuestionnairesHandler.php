<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Query\Questionnaire\ListQuestionnaires;

use Liangjin0228\Questionnaire\Application\DTO\Output\PaginatedResult;
use Liangjin0228\Questionnaire\Application\DTO\Output\QuestionnaireOutput;
use Liangjin0228\Questionnaire\Contracts\Application\QueryHandlerInterface;
use Liangjin0228\Questionnaire\Contracts\Application\QueryInterface;

final readonly class ListQuestionnairesHandler implements QueryHandlerInterface
{
    /**
     * @return PaginatedResult<QuestionnaireOutput>
     */
    public function handle(QueryInterface $query): PaginatedResult
    {
        throw new \RuntimeException('TODO: Implement after Phase 5.4 (Read Models) - Needs QuestionnaireModel and QuestionnaireMapper');
    }
}
