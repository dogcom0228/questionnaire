<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Event;

use Carbon\CarbonImmutable;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Shared\Event\DomainEvent;

final class QuestionnairePublished extends DomainEvent
{
    public function __construct(
        public readonly QuestionnaireId $questionnaireId,
        public readonly CarbonImmutable $publishedAt,
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
            'published_at' => $this->publishedAt->toIso8601String(),
            'occurred_at' => $this->occurredAt->toIso8601String(),
        ];
    }
}
