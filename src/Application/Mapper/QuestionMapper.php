<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Mapper;

use Liangjin0228\Questionnaire\Application\DTO\Output\QuestionOutput;

final readonly class QuestionMapper
{
    public function toOutput(mixed $model): QuestionOutput
    {
        throw new \RuntimeException('TODO: Implement after Phase 5.4 (Read Models) - Needs QuestionModel');
    }
}
