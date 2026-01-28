<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Enum;

enum DuplicateSubmissionStrategy: string
{
    case ALLOW_MULTIPLE = 'allow_multiple';
    case PREVENT_BY_IP = 'prevent_by_ip';
    case PREVENT_BY_USER = 'prevent_by_user';
    case PREVENT_BY_COOKIE = 'prevent_by_cookie';

    public function label(): string
    {
        return match ($this) {
            self::ALLOW_MULTIPLE => 'Allow Multiple Submissions',
            self::PREVENT_BY_IP => 'Prevent by IP Address',
            self::PREVENT_BY_USER => 'Prevent by User ID',
            self::PREVENT_BY_COOKIE => 'Prevent by Cookie',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ALLOW_MULTIPLE => 'Users can submit multiple responses',
            self::PREVENT_BY_IP => 'Prevent duplicate submissions from the same IP address',
            self::PREVENT_BY_USER => 'Prevent duplicate submissions from authenticated users',
            self::PREVENT_BY_COOKIE => 'Prevent duplicate submissions using browser cookies',
        };
    }
}
