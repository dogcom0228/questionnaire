<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Event;

use Carbon\CarbonImmutable;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\DateRange;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSettings;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSlug;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle;
use Liangjin0228\Questionnaire\Domain\Shared\Event\DomainEvent;

final class QuestionnaireUpdated extends DomainEvent
{
    public function __construct(
        public readonly QuestionnaireId $questionnaireId,
        public readonly QuestionnaireTitle $title,
        public readonly QuestionnaireSlug $slug,
        public readonly ?string $description,
        public readonly DateRange $dateRange,
        public readonly QuestionnaireSettings $settings,
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
            'title' => $this->title->value(),
            'slug' => $this->slug->value(),
            'description' => $this->description,
            'date_range' => [
                'starts_at' => $this->dateRange->startsAt()?->toIso8601String(),
                'ends_at' => $this->dateRange->endsAt()?->toIso8601String(),
            ],
            'settings' => $this->settings->toArray(),
            'occurred_at' => $this->occurredAt->toIso8601String(),
        ];
    }
}
