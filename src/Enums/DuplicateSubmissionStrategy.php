<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Enums;

/**
 * Enum representing duplicate submission strategies.
 *
 * Determines how the system handles multiple responses from the same source
 * (user, session, or IP address).
 */
enum DuplicateSubmissionStrategy: string
{
    case ALLOW_MULTIPLE = 'allow_multiple';
    case ONE_PER_USER = 'one_per_user';
    case ONE_PER_SESSION = 'one_per_session';
    case ONE_PER_IP = 'one_per_ip';

    /**
     * Get the human-readable label for the strategy.
     */
    public function label(): string
    {
        return match ($this) {
            self::ALLOW_MULTIPLE => 'Allow Multiple Submissions',
            self::ONE_PER_USER => 'One Per User',
            self::ONE_PER_SESSION => 'One Per Session',
            self::ONE_PER_IP => 'One Per IP Address',
        };
    }

    /**
     * Get all strategies as an associative array (value => label).
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return array_combine(
            array_map(fn (self $strategy) => $strategy->value, self::cases()),
            array_map(fn (self $strategy) => $strategy->label(), self::cases())
        );
    }
}
