<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Question\Enums;

enum QuestionType: string
{
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case NUMBER = 'number';
    case DATE = 'date';
    case RADIO = 'radio';
    case CHECKBOX = 'checkbox';
    case SELECT = 'select';
}
