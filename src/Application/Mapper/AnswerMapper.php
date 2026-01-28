<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Mapper;

use Liangjin0228\Questionnaire\Application\DTO\Output\AnswerOutput;

final readonly class AnswerMapper
{
    public function toOutput(mixed $model): AnswerOutput
    {
        throw new \RuntimeException('TODO: Implement after Phase 5.4 (Read Models) - Needs AnswerModel');
    }
}
