<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts;

use Illuminate\Contracts\Validation\Rule;
use Liangjin0228\Questionnaire\Models\Question;

interface QuestionTypeInterface
{
    /**
     * Get the unique identifier for this question type.
     */
    public function getIdentifier(): string;

    /**
     * Get the display name for this question type.
     */
    public function getName(): string;

    /**
     * Get the description for this question type.
     */
    public function getDescription(): string;

    /**
     * Get the icon (e.g., MDI icon name) for this question type.
     */
    public function getIcon(): string;

    /**
     * Check if this question type supports options (e.g., choices for radio/checkbox).
     */
    public function supportsOptions(): bool;

    /**
     * Get the validation rules for the answer value.
     *
     * @return array<string, mixed>|Rule[]
     */
    public function getValidationRules(Question $question): array;

    /**
     * Get the validation messages for the answer value.
     *
     * @return array<string, string>
     */
    public function getValidationMessages(): array;

    /**
     * Transform the raw answer value before storing.
     *
     * @param mixed $value
     * @return mixed
     */
    public function transformValue(mixed $value): mixed;

    /**
     * Format the answer value for display.
     *
     * @param mixed $value
     */
    public function formatValue(mixed $value, Question $question): string;

    /**
     * Get the Vue component name for rendering this question type.
     */
    public function getVueComponent(): string;

    /**
     * Get additional configuration/metadata for this question type.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array;

    /**
     * Serialize the question type for frontend consumption.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
