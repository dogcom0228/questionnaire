<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\DTO\Output;

final readonly class StatisticsOutput
{
    /**
     * @param  array<QuestionStatistics>  $questionStatistics
     */
    public function __construct(
        public string $questionnaireId,
        public int $totalResponses,
        public ?string $firstResponseAt,
        public ?string $lastResponseAt,
        public array $questionStatistics,
        public float $completionRate,
        public float $averageCompletionTime
    ) {}
}
