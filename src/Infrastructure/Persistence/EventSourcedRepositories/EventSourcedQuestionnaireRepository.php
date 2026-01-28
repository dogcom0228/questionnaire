<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Persistence\EventSourcedRepositories;

use Liangjin0228\Questionnaire\Contracts\EventSourcedQuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;

final class EventSourcedQuestionnaireRepository implements EventSourcedQuestionnaireRepositoryInterface
{
    public function retrieve(QuestionnaireId $id): Questionnaire
    {
        return Questionnaire::retrieve($id->toUuid());
    }

    public function persist(Questionnaire $questionnaire): void
    {
        $questionnaire->persist();
    }
}
