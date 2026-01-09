<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Exceptions;

use Illuminate\Contracts\Validation\Validator;

class ValidationException extends \Illuminate\Validation\ValidationException
{
    public function __construct(Validator $validator)
    {
        parent::__construct($validator);
    }
}
