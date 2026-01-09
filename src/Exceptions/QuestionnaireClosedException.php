<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Exceptions;

use Exception;

class QuestionnaireClosedException extends Exception
{
    public function __construct(string $message = 'This questionnaire is not accepting responses.')
    {
        parent::__construct($message);
    }
}
