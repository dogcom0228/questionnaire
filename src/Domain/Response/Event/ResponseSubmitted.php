<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Response\Event;

use Carbon\CarbonImmutable;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Response\Entity\Answer;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\IpAddress;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\Respondent;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\ResponseId;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\UserAgent;
use Liangjin0228\Questionnaire\Domain\Shared\Event\DomainEvent;

final class ResponseSubmitted extends DomainEvent
{
    /**
     * @param  array<string, Answer>  $answers
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly ResponseId $responseId,
        public readonly QuestionnaireId $questionnaireId,
        public readonly Respondent $respondent,
        public readonly IpAddress $ipAddress,
        public readonly UserAgent $userAgent,
        public readonly array $answers,
        public readonly array $metadata,
        public readonly CarbonImmutable $submittedAt,
    ) {
        parent::__construct();
        $this->setAggregateRootUuid($responseId->toString());
        $this->setOccurredAt($submittedAt);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $answersArray = [];
        foreach ($this->answers as $questionId => $answer) {
            $answersArray[$questionId] = [
                'answer_id' => $answer->id()->toString(),
                'question_id' => $answer->questionId()->toString(),
                'value' => $answer->value()->toMixed(),
            ];
        }

        return [
            'response_id' => $this->responseId->toString(),
            'questionnaire_id' => $this->questionnaireId->toString(),
            'respondent' => [
                'type' => $this->respondent->type(),
                'id' => $this->respondent->id(),
            ],
            'ip_address' => $this->ipAddress->toString(),
            'user_agent' => $this->userAgent->toString(),
            'answers' => $answersArray,
            'metadata' => $this->metadata,
            'submitted_at' => $this->submittedAt->toIso8601String(),
        ];
    }
}
