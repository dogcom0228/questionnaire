<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate;

use Carbon\CarbonImmutable;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Entity\Question;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Enum\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionAdded;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnaireClosed;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnaireCreated;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnairePublished;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnaireUpdated;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionRemoved;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionUpdated;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Exception\InvalidQuestionnaireStateTransitionException;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\DateRange;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSettings;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSlug;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle;
use Liangjin0228\Questionnaire\Domain\Shared\Aggregate\AggregateRoot;
use Ramsey\Uuid\UuidInterface;

final class Questionnaire extends AggregateRoot
{
    private QuestionnaireId $id;

    private QuestionnaireTitle $title;

    private QuestionnaireSlug $slug;

    private ?string $description = null;

    private QuestionnaireStatus $status;

    private DateRange $dateRange;

    private QuestionnaireSettings $settings;

    private ?CarbonImmutable $publishedAt = null;

    private ?CarbonImmutable $closedAt = null;

    /**
     * @var array<string, Question>
     */
    private array $questions = [];

    public static function create(
        QuestionnaireId $id,
        QuestionnaireTitle $title,
        QuestionnaireSlug $slug,
        ?string $description,
        DateRange $dateRange,
        QuestionnaireSettings $settings
    ): self {
        $questionnaire = new self;

        $questionnaire->recordThat(
            new QuestionnaireCreated(
                $id,
                $title,
                $slug,
                $description,
                $dateRange,
                $settings
            )
        );

        return $questionnaire;
    }

    public function update(
        QuestionnaireTitle $title,
        QuestionnaireSlug $slug,
        ?string $description,
        DateRange $dateRange,
        QuestionnaireSettings $settings
    ): void {
        $this->recordThat(
            new QuestionnaireUpdated(
                $this->id,
                $title,
                $slug,
                $description,
                $dateRange,
                $settings
            )
        );
    }

    public function publish(): void
    {
        if (empty($this->questions)) {
            throw InvalidQuestionnaireStateTransitionException::cannotPublishWithoutQuestions();
        }

        if (! $this->status->canTransitionTo(QuestionnaireStatus::PUBLISHED)) {
            throw InvalidQuestionnaireStateTransitionException::cannotTransition(
                $this->status,
                QuestionnaireStatus::PUBLISHED
            );
        }

        $this->recordThat(
            new QuestionnairePublished(
                $this->id,
                CarbonImmutable::now()
            )
        );
    }

    public function close(): void
    {
        if (! $this->status->canTransitionTo(QuestionnaireStatus::CLOSED)) {
            throw InvalidQuestionnaireStateTransitionException::cannotTransition(
                $this->status,
                QuestionnaireStatus::CLOSED
            );
        }

        $this->recordThat(
            new QuestionnaireClosed(
                $this->id,
                CarbonImmutable::now()
            )
        );
    }

    public function addQuestion(Question $question): void
    {
        if ($this->status->isPublished()) {
            throw InvalidQuestionnaireStateTransitionException::cannotAddQuestionWhenPublished();
        }

        $this->recordThat(
            new QuestionAdded(
                $this->id,
                $question
            )
        );
    }

    public function updateQuestion(Question $question): void
    {
        $questionId = $question->id()->toString();

        if (! isset($this->questions[$questionId])) {
            throw InvalidQuestionnaireStateTransitionException::questionNotFound($questionId);
        }

        $this->recordThat(
            new QuestionUpdated(
                $this->id,
                $question
            )
        );
    }

    public function removeQuestion(QuestionId $questionId): void
    {
        if ($this->status->isPublished()) {
            throw InvalidQuestionnaireStateTransitionException::cannotRemoveQuestionWhenPublished();
        }

        $questionIdString = $questionId->toString();

        if (! isset($this->questions[$questionIdString])) {
            throw InvalidQuestionnaireStateTransitionException::questionNotFound($questionIdString);
        }

        $this->recordThat(
            new QuestionRemoved(
                $this->id,
                $questionId
            )
        );
    }

    public function getAggregateRootId(): UuidInterface
    {
        return $this->id->toUuid();
    }

    public function id(): QuestionnaireId
    {
        return $this->id;
    }

    public function title(): QuestionnaireTitle
    {
        return $this->title;
    }

    public function slug(): QuestionnaireSlug
    {
        return $this->slug;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function status(): QuestionnaireStatus
    {
        return $this->status;
    }

    public function dateRange(): DateRange
    {
        return $this->dateRange;
    }

    public function settings(): QuestionnaireSettings
    {
        return $this->settings;
    }

    public function publishedAt(): ?CarbonImmutable
    {
        return $this->publishedAt;
    }

    public function closedAt(): ?CarbonImmutable
    {
        return $this->closedAt;
    }

    /**
     * @return array<string, Question>
     */
    public function questions(): array
    {
        return $this->questions;
    }

    public function hasQuestions(): bool
    {
        return ! empty($this->questions);
    }

    public function questionCount(): int
    {
        return count($this->questions);
    }

    public function isActive(?CarbonImmutable $at = null): bool
    {
        return $this->status->isPublished() && $this->dateRange->isActive($at);
    }

    protected function applyQuestionnaireCreated(QuestionnaireCreated $event): void
    {
        $this->id = $event->questionnaireId;
        $this->title = $event->title;
        $this->slug = $event->slug;
        $this->description = $event->description;
        $this->dateRange = $event->dateRange;
        $this->settings = $event->settings;
        $this->status = QuestionnaireStatus::DRAFT;
    }

    protected function applyQuestionnaireUpdated(QuestionnaireUpdated $event): void
    {
        $this->title = $event->title;
        $this->slug = $event->slug;
        $this->description = $event->description;
        $this->dateRange = $event->dateRange;
        $this->settings = $event->settings;
    }

    protected function applyQuestionnairePublished(QuestionnairePublished $event): void
    {
        $this->status = QuestionnaireStatus::PUBLISHED;
        $this->publishedAt = $event->publishedAt;
    }

    protected function applyQuestionnaireClosed(QuestionnaireClosed $event): void
    {
        $this->status = QuestionnaireStatus::CLOSED;
        $this->closedAt = $event->closedAt;
    }

    protected function applyQuestionAdded(QuestionAdded $event): void
    {
        $this->questions[$event->question->id()->toString()] = $event->question;
    }

    protected function applyQuestionUpdated(QuestionUpdated $event): void
    {
        $this->questions[$event->question->id()->toString()] = $event->question;
    }

    protected function applyQuestionRemoved(QuestionRemoved $event): void
    {
        unset($this->questions[$event->questionId->toString()]);
    }
}
