<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Response\Entity;

use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionId;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\AnswerId;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\AnswerValue;
use Liangjin0228\Questionnaire\Domain\Shared\Entity\Entity;

final class Answer extends Entity
{
    private function __construct(
        AnswerId $id,
        private readonly QuestionId $questionId,
        private AnswerValue $value
    ) {
        parent::__construct($id);
    }

    public static function create(
        AnswerId $id,
        QuestionId $questionId,
        AnswerValue $value
    ): self {
        return new self($id, $questionId, $value);
    }

    public function questionId(): QuestionId
    {
        return $this->questionId;
    }

    public function value(): AnswerValue
    {
        return $this->value;
    }

    public function updateValue(AnswerValue $newValue): void
    {
        $this->value = $newValue;
    }
}
