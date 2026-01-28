<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Http\Request;

use Illuminate\Foundation\Http\FormRequest;
use Liangjin0228\Questionnaire\Domain\Question\Enums\QuestionType;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! config('questionnaire.features.authorization', true)) {
            return true;
        }

        $questionnaire = $this->route('questionnaire');

        if ($questionnaire instanceof Questionnaire) {
            return $this->user()?->can('update', $questionnaire) ?? false;
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $questionnaire = $this->route('questionnaire');

        $questionTypes = implode(',', array_map(static fn (QuestionType $type) => $type->value, QuestionType::cases()));

        return [
            'content' => ['required', 'string', 'min:3', 'max:1000'],
            'type' => ['required', 'string', 'in:'.$questionTypes],
            'description' => ['nullable', 'string', 'max:2000'],
            'options' => ['nullable', 'array', 'min:2', 'max:50'],
            'options.*' => ['string', 'max:500', 'distinct'],
            'required' => ['nullable', 'boolean'],
            'order' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'settings' => ['nullable', 'array', 'max:20'],
            'settings.min' => ['nullable', 'numeric'],
            'settings.max' => ['nullable', 'numeric', 'gte:settings.min'],
            'settings.max_length' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'settings.placeholder' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toDto(): \Liangjin0228\Questionnaire\DTOs\QuestionData
    {
        /** @var array{type: string, content: string, description?: string|null, options?: array<int|string, mixed>|null, required?: bool, order?: int, settings?: array<string, mixed>} $validated */
        $validated = $this->validated();

        return \Liangjin0228\Questionnaire\DTOs\QuestionData::fromStringType(
            type: $validated['type'],
            content: $validated['content'],
            description: $validated['description'] ?? null,
            options: $validated['options'] ?? null,
            required: $validated['required'] ?? false,
            order: $validated['order'] ?? 0,
            settings: $validated['settings'] ?? [],
        );
    }
}
