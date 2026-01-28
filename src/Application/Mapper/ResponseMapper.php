<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Mapper;

use Liangjin0228\Questionnaire\Application\DTO\Output\ResponseOutput;

final readonly class ResponseMapper
{
    public function __construct(
        private AnswerMapper $answerMapper
    ) {}

    public function toOutput(mixed $model): ResponseOutput
    {
        throw new \RuntimeException('TODO: Implement after Phase 5.4 (Read Models) - Needs ResponseModel and AnswerMapper->toOutput()');
    }
}
