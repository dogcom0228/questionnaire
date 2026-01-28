<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\DTO\Output;

final readonly class QuestionStatistics
{
    public function __construct(
        public string $questionId,
        public string $questionTitle,
        public string $questionType,
        public int $totalResponses,
        public mixed $statistics
    ) {}
}
