<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\DTOs;

use Liangjin0228\Questionnaire\Enums\DuplicateSubmissionStrategy;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Enums\QuestionnaireStatus;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

/**
 * Data Transfer Object for Questionnaire data.
 *
 * This DTO encapsulates all the data needed to create or update a questionnaire.
 * It enforces type safety and provides a clear contract for the data structure.
 */
class QuestionnaireData extends Data
{
    /**
     * @param  string  $title  The questionnaire title
     * @param  string|null  $description  Optional description
     * @param  string|null  $slug  URL-friendly slug (auto-generated if not provided)
     * @param  QuestionnaireStatus  $status  Current status (draft, published, closed)
     * @param  array<string, mixed>|null  $settings  Additional settings
     * @param  string|null  $starts_at  When the questionnaire starts accepting responses
     * @param  string|null  $ends_at  When the questionnaire stops accepting responses
     * @param  bool  $requires_auth  Whether authentication is required to respond
     * @param  int|null  $submission_limit  Maximum number of submissions allowed
     * @param  DuplicateSubmissionStrategy  $duplicate_submission_strategy  Strategy for handling duplicate submissions
     * @param  array<int, QuestionData>  $questions  Array of questions
     */
    public function __construct(
        public string $title,
        public ?string $description = null,
        public ?string $slug = null,
        #[WithCast(EnumCast::class)]
        public QuestionnaireStatus $status = QuestionnaireStatus::DRAFT,
        public ?array $settings = [],
        public ?string $starts_at = null,
        public ?string $ends_at = null,
        public bool $requires_auth = false,
        public ?int $submission_limit = null,
        #[WithCast(EnumCast::class)]
        public DuplicateSubmissionStrategy $duplicate_submission_strategy = DuplicateSubmissionStrategy::ALLOW_MULTIPLE,
        #[DataCollectionOf(QuestionData::class)]
        public ?array $questions = [],
    ) {}

    /**
     * Check if the questionnaire has questions.
     */
    public function hasQuestions(): bool
    {
        return ! empty($this->questions);
    }

    /**
     * Get the number of questions.
     */
    public function questionCount(): int
    {
        return count($this->questions ?? []);
    }
}
