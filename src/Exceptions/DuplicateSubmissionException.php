<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Exceptions;

use Exception;

class DuplicateSubmissionException extends Exception
{
    public function __construct(string $message = 'Duplicate submission detected.')
    {
        parent::__construct($message);
    }
}
