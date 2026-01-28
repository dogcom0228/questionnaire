<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts;

use Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;

interface EventSourcedQuestionnaireRepositoryInterface
{
    public function retrieve(QuestionnaireId $id): Questionnaire;

    public function persist(Questionnaire $questionnaire): void;
}
