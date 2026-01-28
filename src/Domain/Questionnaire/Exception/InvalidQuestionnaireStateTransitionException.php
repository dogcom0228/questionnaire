<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Exception;

use Liangjin0228\Questionnaire\Domain\Questionnaire\Enum\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Domain\Shared\Exception\DomainException;

final class InvalidQuestionnaireStateTransitionException extends DomainException
{
    public static function cannotTransition(QuestionnaireStatus $from, QuestionnaireStatus $to): self
    {
        return new self(
            sprintf(
                'Cannot transition questionnaire status from "%s" to "%s".',
                $from->value,
                $to->value
            )
        );
    }

    public static function cannotPublishWithoutQuestions(): self
    {
        return new self('Cannot publish a questionnaire without questions.');
    }

    public static function cannotAddQuestionWhenPublished(): self
    {
        return new self('Cannot add questions to a published questionnaire.');
    }

    public static function cannotRemoveQuestionWhenPublished(): self
    {
        return new self('Cannot remove questions from a published questionnaire.');
    }

    public static function questionNotFound(string $questionId): self
    {
        return new self(sprintf('Question with ID "%s" not found.', $questionId));
    }
}
