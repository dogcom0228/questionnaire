<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Event;

use Carbon\CarbonImmutable;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Entity\Question;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Shared\Event\DomainEvent;

final class QuestionUpdated extends DomainEvent
{
    public function __construct(
        public readonly QuestionnaireId $questionnaireId,
        public readonly Question $question,
        ?CarbonImmutable $occurredAt = null
    ) {
        parent::__construct();
        $this->setAggregateRootUuid($questionnaireId->toString());
        if ($occurredAt) {
            $this->setOccurredAt($occurredAt);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'questionnaire_id' => $this->questionnaireId->toString(),
            'question' => [
                'id' => $this->question->id()->toString(),
                'text' => $this->question->text()->value(),
                'type' => $this->question->type(),
                'options' => $this->question->options()->value(),
                'required' => $this->question->isRequired(),
                'order' => $this->question->order(),
                'description' => $this->question->description(),
                'settings' => $this->question->settings(),
            ],
            'occurred_at' => $this->occurredAt->toIso8601String(),
        ];
    }
}
