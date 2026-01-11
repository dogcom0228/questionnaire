<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\DTOs;

use Liangjin0228\Questionnaire\Enums\QuestionType;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

/**
 * Data Transfer Object for Question data.
 *
 * Represents the structured data for a question within a questionnaire.
 * Uses strict typing with Enums to ensure type safety.
 */
class QuestionData extends Data
{
    /**
     * @param  QuestionType  $type  The question type (text, radio, checkbox, etc.)
     * @param  string  $content  The question content/text
     * @param  string|null  $description  Optional description or help text
     * @param  array<string>|null  $options  Options for choice-based questions
     * @param  bool  $required  Whether the question requires an answer
     * @param  int  $order  Display order of the question
     * @param  array<string, mixed>|null  $settings  Additional question-specific settings
     * @param  int|null  $id  The question ID (for updates only)
     */
    public function __construct(
        #[WithCast(EnumCast::class)]
        public QuestionType $type,
        public string $content,
        public ?string $description = null,
        public ?array $options = null,
        public bool $required = false,
        public int $order = 0,
        public ?array $settings = [],
        public ?int $id = null,
    ) {}

    /**
     * Create a QuestionData instance from a string type.
     *
     * This factory method allows backward compatibility with string-based types.
     */
    public static function fromStringType(
        string $type,
        string $content,
        ?string $description = null,
        ?array $options = null,
        bool $required = false,
        int $order = 0,
        ?array $settings = [],
        ?int $id = null,
    ): self {
        return new self(
            type: QuestionType::from($type),
            content: $content,
            description: $description,
            options: $options,
            required: $required,
            order: $order,
            settings: $settings,
            id: $id,
        );
    }
}
