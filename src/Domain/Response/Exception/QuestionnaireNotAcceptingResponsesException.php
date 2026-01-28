<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Response\Exception;

use Liangjin0228\Questionnaire\Domain\Questionnaire\Enum\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Shared\Exception\DomainException;

final class QuestionnaireNotAcceptingResponsesException extends DomainException
{
    public static function notPublished(QuestionnaireId $questionnaireId, QuestionnaireStatus $currentStatus): self
    {
        return new self(
            sprintf(
                'Questionnaire "%s" is not accepting responses. Current status: %s',
                $questionnaireId->toString(),
                $currentStatus->value
            )
        );
    }

    public static function notInDateRange(QuestionnaireId $questionnaireId): self
    {
        return new self(
            sprintf(
                'Questionnaire "%s" is not accepting responses at this time (outside active date range)',
                $questionnaireId->toString()
            )
        );
    }

    public static function closed(QuestionnaireId $questionnaireId): self
    {
        return new self(
            sprintf(
                'Questionnaire "%s" has been closed and is no longer accepting responses',
                $questionnaireId->toString()
            )
        );
    }
}
