<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Response\Exception;

use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionId;
use Liangjin0228\Questionnaire\Domain\Shared\Exception\DomainException;

final class InvalidAnswerException extends DomainException
{
    public static function questionNotFound(QuestionId $questionId): self
    {
        return new self(
            sprintf(
                'Question with ID "%s" not found in questionnaire',
                $questionId->toString()
            )
        );
    }

    public static function invalidValue(QuestionId $questionId, string $reason): self
    {
        return new self(
            sprintf(
                'Invalid answer value for question "%s": %s',
                $questionId->toString(),
                $reason
            )
        );
    }

    public static function missingRequiredAnswer(QuestionId $questionId): self
    {
        return new self(
            sprintf(
                'Missing required answer for question "%s"',
                $questionId->toString()
            )
        );
    }
}
