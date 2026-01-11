<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Enums;

enum QuestionType: string
{
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case RADIO = 'radio';
    case CHECKBOX = 'checkbox';
    case SELECT = 'select';
    case NUMBER = 'number';
    case DATE = 'date';
}
