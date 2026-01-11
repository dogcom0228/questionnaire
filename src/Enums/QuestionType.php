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

    /**
     * Get the human-readable label for the question type.
     */
    public function label(): string
    {
        return match ($this) {
            self::TEXT => 'Text',
            self::TEXTAREA => 'Text Area',
            self::RADIO => 'Radio',
            self::CHECKBOX => 'Checkbox',
            self::SELECT => 'Select',
            self::NUMBER => 'Number',
            self::DATE => 'Date',
        };
    }
}
