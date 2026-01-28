<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Query\Response\GetStatistics;

use Liangjin0228\Questionnaire\Application\DTO\Output\StatisticsOutput;
use Liangjin0228\Questionnaire\Contracts\Application\QueryHandlerInterface;
use Liangjin0228\Questionnaire\Contracts\Application\QueryInterface;

final readonly class GetStatisticsHandler implements QueryHandlerInterface
{
    public function handle(QueryInterface $query): StatisticsOutput
    {
        throw new \RuntimeException('TODO: Implement after Phase 5.4 (Read Models) and Domain Service - Needs QuestionnaireModel, ResponseModel, and StatisticsCalculationService');
    }
}
