<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\DTOs;

use Spatie\LaravelData\Data;

/**
 * Data Transfer Object for Response submission data.
 *
 * This DTO encapsulates all data needed when submitting a response to a questionnaire.
 * Using a DTO ensures type safety and provides a clear contract for the submission flow.
 */
class SubmitResponseData extends Data
{
    /**
     * @param  array<int|string, mixed>  $answers  The answers keyed by question ID
     * @param  int|string|null  $userId  The authenticated user's ID (if any)
     * @param  string|null  $sessionId  The session ID for tracking (for one_per_session strategy)
     * @param  string|null  $ipAddress  The IP address (for one_per_ip strategy)
     * @param  array<string, mixed>  $metadata  Additional metadata about the submission
     */
    public function __construct(
        public array $answers,
        public int|string|null $userId = null,
        public ?string $sessionId = null,
        public ?string $ipAddress = null,
        public array $metadata = [],
    ) {}

    /**
     * Get the answer for a specific question.
     *
     * @param  int|string  $questionId
     * @return mixed
     */
    public function getAnswer(int|string $questionId): mixed
    {
        return $this->answers[$questionId] ?? null;
    }

    /**
     * Check if an answer exists for a specific question.
     */
    public function hasAnswer(int|string $questionId): bool
    {
        return array_key_exists($questionId, $this->answers);
    }
}
