<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Mapper;

use Liangjin0228\Questionnaire\Application\DTO\Output\QuestionnaireOutput;

final readonly class QuestionnaireMapper
{
    public function __construct(
        private QuestionMapper $questionMapper
    ) {}

    public function toOutput(mixed $model): QuestionnaireOutput
    {
        throw new \RuntimeException('TODO: Implement after Phase 5.4 (Read Models) - Needs QuestionnaireModel and QuestionMapper->toOutput()');
    }
}
