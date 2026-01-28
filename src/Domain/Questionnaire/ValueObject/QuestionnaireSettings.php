<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject;

use Liangjin0228\Questionnaire\Domain\Questionnaire\Enum\DuplicateSubmissionStrategy;
use Liangjin0228\Questionnaire\Domain\Shared\ValueObject\ValueObject;

final class QuestionnaireSettings extends ValueObject
{
    private function __construct(
        private readonly bool $allowAnonymous,
        private readonly bool $allowMultipleSubmissions,
        private readonly DuplicateSubmissionStrategy $duplicateSubmissionStrategy,
        private readonly bool $sendNotifications,
        private readonly ?string $notificationEmail,
        private readonly int $maxSubmissions
    ) {}

    public static function create(
        bool $allowAnonymous = true,
        bool $allowMultipleSubmissions = false,
        ?DuplicateSubmissionStrategy $duplicateSubmissionStrategy = null,
        bool $sendNotifications = false,
        ?string $notificationEmail = null,
        int $maxSubmissions = 0
    ): self {
        return new self(
            $allowAnonymous,
            $allowMultipleSubmissions,
            $duplicateSubmissionStrategy ?? DuplicateSubmissionStrategy::ALLOW_MULTIPLE,
            $sendNotifications,
            $notificationEmail,
            $maxSubmissions
        );
    }

    public static function default(): self
    {
        return new self(
            allowAnonymous: true,
            allowMultipleSubmissions: false,
            duplicateSubmissionStrategy: DuplicateSubmissionStrategy::ALLOW_MULTIPLE,
            sendNotifications: false,
            notificationEmail: null,
            maxSubmissions: 0
        );
    }

    public function allowAnonymous(): bool
    {
        return $this->allowAnonymous;
    }

    public function allowMultipleSubmissions(): bool
    {
        return $this->allowMultipleSubmissions;
    }

    public function duplicateSubmissionStrategy(): DuplicateSubmissionStrategy
    {
        return $this->duplicateSubmissionStrategy;
    }

    public function sendNotifications(): bool
    {
        return $this->sendNotifications;
    }

    public function notificationEmail(): ?string
    {
        return $this->notificationEmail;
    }

    public function maxSubmissions(): int
    {
        return $this->maxSubmissions;
    }

    public function hasMaxSubmissions(): bool
    {
        return $this->maxSubmissions > 0;
    }

    public function equals(ValueObject $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        return $this->allowAnonymous === $other->allowAnonymous
            && $this->allowMultipleSubmissions === $other->allowMultipleSubmissions
            && $this->duplicateSubmissionStrategy === $other->duplicateSubmissionStrategy
            && $this->sendNotifications === $other->sendNotifications
            && $this->notificationEmail === $other->notificationEmail
            && $this->maxSubmissions === $other->maxSubmissions;
    }

    /**
     * @return array<string, mixed>
     */
    public function value(): array
    {
        return $this->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'allow_anonymous' => $this->allowAnonymous,
            'allow_multiple_submissions' => $this->allowMultipleSubmissions,
            'duplicate_submission_strategy' => $this->duplicateSubmissionStrategy->value,
            'send_notifications' => $this->sendNotifications,
            'notification_email' => $this->notificationEmail,
            'max_submissions' => $this->maxSubmissions,
        ];
    }
}
