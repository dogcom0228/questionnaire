<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Response\Aggregate;

use Carbon\CarbonImmutable;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Response\Entity\Answer;
use Liangjin0228\Questionnaire\Domain\Response\Event\ResponseSubmitted;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\IpAddress;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\Respondent;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\ResponseId;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\UserAgent;
use Liangjin0228\Questionnaire\Domain\Shared\Aggregate\AggregateRoot;
use Ramsey\Uuid\UuidInterface;

final class Response extends AggregateRoot
{
    private ResponseId $id;

    private QuestionnaireId $questionnaireId;

    private Respondent $respondent;

    private IpAddress $ipAddress;

    private UserAgent $userAgent;

    /**
     * @var array<string, Answer>
     */
    private array $answers = [];

    /**
     * @var array<string, mixed>
     */
    private array $metadata = [];

    private CarbonImmutable $submittedAt;

    /**
     * @param  array<string, Answer>  $answers
     * @param  array<string, mixed>  $metadata
     */
    public static function submit(
        ResponseId $id,
        QuestionnaireId $questionnaireId,
        Respondent $respondent,
        IpAddress $ipAddress,
        UserAgent $userAgent,
        array $answers,
        array $metadata = []
    ): self {
        $response = new self;

        $response->recordThat(
            new ResponseSubmitted(
                $id,
                $questionnaireId,
                $respondent,
                $ipAddress,
                $userAgent,
                $answers,
                $metadata,
                CarbonImmutable::now()
            )
        );

        return $response;
    }

    public function getAggregateRootId(): UuidInterface
    {
        return $this->id->toUuid();
    }

    public function id(): ResponseId
    {
        return $this->id;
    }

    public function questionnaireId(): QuestionnaireId
    {
        return $this->questionnaireId;
    }

    public function respondent(): Respondent
    {
        return $this->respondent;
    }

    public function ipAddress(): IpAddress
    {
        return $this->ipAddress;
    }

    public function userAgent(): UserAgent
    {
        return $this->userAgent;
    }

    /**
     * @return array<string, Answer>
     */
    public function answers(): array
    {
        return $this->answers;
    }

    public function getAnswer(QuestionId $questionId): ?Answer
    {
        return $this->answers[$questionId->toString()] ?? null;
    }

    public function hasAnswer(QuestionId $questionId): bool
    {
        return isset($this->answers[$questionId->toString()]);
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    public function submittedAt(): CarbonImmutable
    {
        return $this->submittedAt;
    }

    public function isAuthenticated(): bool
    {
        return $this->respondent->isAuthenticated();
    }

    protected function applyResponseSubmitted(ResponseSubmitted $event): void
    {
        $this->id = $event->responseId;
        $this->questionnaireId = $event->questionnaireId;
        $this->respondent = $event->respondent;
        $this->ipAddress = $event->ipAddress;
        $this->userAgent = $event->userAgent;
        $this->answers = $event->answers;
        $this->metadata = $event->metadata;
        $this->submittedAt = $event->submittedAt;
    }
}
