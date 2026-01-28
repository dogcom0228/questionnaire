<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Query\Response\GetStatistics;

use Liangjin0228\Questionnaire\Contracts\Application\QueryInterface;

final readonly class GetStatisticsQuery implements QueryInterface
{
    public function __construct(
        public string $questionnaireId
    ) {}
}
