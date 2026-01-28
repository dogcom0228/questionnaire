<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Enum;

enum QuestionnaireStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case CLOSED = 'closed';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
            self::CLOSED => 'Closed',
            self::ARCHIVED => 'Archived',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::DRAFT => in_array($newStatus, [self::PUBLISHED, self::ARCHIVED], true),
            self::PUBLISHED => in_array($newStatus, [self::CLOSED, self::ARCHIVED], true),
            self::CLOSED => $newStatus === self::ARCHIVED,
            self::ARCHIVED => false,
        };
    }

    public function isPublished(): bool
    {
        return $this === self::PUBLISHED;
    }

    public function isDraft(): bool
    {
        return $this === self::DRAFT;
    }

    public function isClosed(): bool
    {
        return $this === self::CLOSED;
    }

    public function isArchived(): bool
    {
        return $this === self::ARCHIVED;
    }
}
