<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Projector;

use Liangjin0228\Questionnaire\Contracts\Infrastructure\ProjectorInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionAdded;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnaireClosed;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnaireCreated;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnairePublished;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnaireUpdated;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionRemoved;

final class QuestionnaireProjector implements ProjectorInterface
{
    public function onQuestionnaireCreated(QuestionnaireCreated $event): void
    {
        throw new \RuntimeException('TODO: Implement after Phase 5.4 (Read Models) - Needs QuestionnaireModel');
    }

    public function onQuestionnaireUpdated(QuestionnaireUpdated $event): void
    {
        throw new \RuntimeException('TODO: Implement after Phase 5.4 (Read Models) - Needs QuestionnaireModel');
    }

    public function onQuestionnairePublished(QuestionnairePublished $event): void
    {
        throw new \RuntimeException('TODO: Implement after Phase 5.4 (Read Models) - Needs QuestionnaireModel');
    }

    public function onQuestionnaireClosed(QuestionnaireClosed $event): void
    {
        throw new \RuntimeException('TODO: Implement after Phase 5.4 (Read Models) - Needs QuestionnaireModel');
    }

    public function onQuestionAdded(QuestionAdded $event): void
    {
        throw new \RuntimeException('TODO: Implement after Phase 5.4 (Read Models) - Needs QuestionModel');
    }

    public function onQuestionRemoved(QuestionRemoved $event): void
    {
        throw new \RuntimeException('TODO: Implement after Phase 5.4 (Read Models) - Needs QuestionModel');
    }

    /**
     * @return array<class-string, string>
     */
    public function getSubscribedEvents(): array
    {
        return [
            QuestionnaireCreated::class => 'onQuestionnaireCreated',
            QuestionnaireUpdated::class => 'onQuestionnaireUpdated',
            QuestionnairePublished::class => 'onQuestionnairePublished',
            QuestionnaireClosed::class => 'onQuestionnaireClosed',
            QuestionAdded::class => 'onQuestionAdded',
            QuestionRemoved::class => 'onQuestionRemoved',
        ];
    }

    public function reset(): void
    {
        throw new \RuntimeException('TODO: Implement after Phase 5.4 (Read Models) - Needs QuestionModel and QuestionnaireModel');
    }
}
