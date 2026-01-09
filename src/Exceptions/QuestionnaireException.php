<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Exceptions;

use Exception;

class QuestionnaireException extends Exception
{
    public static function alreadyPublished(): self
    {
        return new self('This questionnaire is already published.');
    }

    public static function alreadyClosed(): self
    {
        return new self('This questionnaire is already closed.');
    }

    public static function cannotPublishClosed(): self
    {
        return new self('Cannot publish a closed questionnaire.');
    }

    public static function noQuestions(): self
    {
        return new self('Cannot publish a questionnaire without questions.');
    }

    public static function notFound(): self
    {
        return new self('Questionnaire not found.');
    }

    public static function invalidStatus(string $status): self
    {
        return new self("Invalid questionnaire status: {$status}");
    }
}
