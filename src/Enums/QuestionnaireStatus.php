<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Enums;

enum QuestionnaireStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case CLOSED = 'closed';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
            self::CLOSED => 'Closed',
            self::ARCHIVED => 'Archived',
        };
    }
}
