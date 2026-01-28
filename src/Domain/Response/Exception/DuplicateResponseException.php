<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Response\Exception;

use Liangjin0228\Questionnaire\Domain\Questionnaire\Enum\DuplicateSubmissionStrategy;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Shared\Exception\DomainException;

final class DuplicateResponseException extends DomainException
{
    public static function forQuestionnaire(
        QuestionnaireId $questionnaireId,
        DuplicateSubmissionStrategy $strategy,
        string $identifier
    ): self {
        return new self(
            sprintf(
                'Duplicate response detected for questionnaire "%s". Strategy: %s, Identifier: %s',
                $questionnaireId->toString(),
                $strategy->value,
                $identifier
            )
        );
    }

    public static function byIp(QuestionnaireId $questionnaireId, string $ipAddress): self
    {
        return new self(
            sprintf(
                'Duplicate response from IP address "%s" for questionnaire "%s"',
                $ipAddress,
                $questionnaireId->toString()
            )
        );
    }

    public static function byUser(QuestionnaireId $questionnaireId, string $userId): self
    {
        return new self(
            sprintf(
                'Duplicate response from user "%s" for questionnaire "%s"',
                $userId,
                $questionnaireId->toString()
            )
        );
    }

    public static function byCookie(QuestionnaireId $questionnaireId, string $cookieValue): self
    {
        return new self(
            sprintf(
                'Duplicate response detected via cookie for questionnaire "%s"',
                $questionnaireId->toString()
            )
        );
    }
}
