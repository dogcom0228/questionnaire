<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Liangjin0228\Questionnaire\Models\Questionnaire;

class UpdateQuestionnaireRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $questionnaireId = $this->route('questionnaire')?->id;
        $allowedQuestionTypes = ['text', 'textarea', 'radio', 'checkbox', 'select', 'number', 'date'];

        $questionnairesTable = config('questionnaire.table_names.questionnaires', 'questionnaires');
        $questionsTable = config('questionnaire.table_names.questions', 'questions');

        return [
            'title' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique($questionnairesTable, 'slug')->ignore($questionnaireId),
            ],
            'settings' => ['nullable', 'array', 'max:50'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'requires_auth' => ['nullable', 'boolean'],
            'submission_limit' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'duplicate_submission_strategy' => ['nullable', 'string', 'in:allow_multiple,one_per_user,one_per_session,one_per_ip'],

            // Questions - Enhanced validation
            'questions' => ['nullable', 'array', 'max:100'],
            'questions.*.id' => ['nullable', 'integer', Rule::exists($questionsTable, 'id')],
            'questions.*.type' => ['required_with:questions', 'string', 'in:'.implode(',', $allowedQuestionTypes)],
            'questions.*.content' => ['required_with:questions', 'string', 'min:3', 'max:1000'],
            'questions.*.description' => ['nullable', 'string', 'max:2000'],
            'questions.*.options' => ['nullable', 'array', 'min:2', 'max:50'],
            'questions.*.options.*' => ['string', 'max:500', 'distinct'],
            'questions.*.required' => ['nullable', 'boolean'],
            'questions.*.order' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'questions.*.settings' => ['nullable', 'array', 'max:20'],
            'questions.*.settings.min' => ['nullable', 'numeric'],
            'questions.*.settings.max' => ['nullable', 'numeric', 'gte:questions.*.settings.min'],
            'questions.*.settings.max_length' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'questions.*.settings.placeholder' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a title for the questionnaire.',
            'ends_at.after_or_equal' => 'The end date must be after or equal to the start date.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('requires_auth')) {
            $this->merge([
                'requires_auth' => filter_var($this->requires_auth, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    /**
     * Convert validated data to QuestionnaireData DTO.
     */
    public function toDto(): \Liangjin0228\Questionnaire\DTOs\QuestionnaireData
    {
        $validated = $this->validated();
        $questionnaire = $this->route('questionnaire');

        // Convert questions to QuestionData DTOs
        $questions = [];
        if (! empty($validated['questions'])) {
            foreach ($validated['questions'] as $question) {
                $questions[] = \Liangjin0228\Questionnaire\DTOs\QuestionData::fromStringType(
                    type: $question['type'],
                    content: $question['content'],
                    description: $question['description'] ?? null,
                    options: $question['options'] ?? null,
                    required: $question['required'] ?? false,
                    order: $question['order'] ?? 0,
                    settings: $question['settings'] ?? [],
                    id: $question['id'] ?? null,
                );
            }
        }

        return new \Liangjin0228\Questionnaire\DTOs\QuestionnaireData(
            title: $validated['title'] ?? $questionnaire->title,
            description: array_key_exists('description', $validated) ? $validated['description'] : $questionnaire->description,
            slug: $validated['slug'] ?? null,
            status: isset($validated['status'])
                ? (\Liangjin0228\Questionnaire\Enums\QuestionnaireStatus::tryFrom($validated['status'])
                    ?? \Liangjin0228\Questionnaire\Enums\QuestionnaireStatus::DRAFT)
                : \Liangjin0228\Questionnaire\Enums\QuestionnaireStatus::tryFrom($questionnaire->status)
                    ?? \Liangjin0228\Questionnaire\Enums\QuestionnaireStatus::DRAFT,
            settings: $validated['settings'] ?? $questionnaire->settings,
            starts_at: $validated['starts_at'] ?? $questionnaire->starts_at?->toDateTimeString(),
            ends_at: $validated['ends_at'] ?? $questionnaire->ends_at?->toDateTimeString(),
            requires_auth: $validated['requires_auth'] ?? $questionnaire->requires_auth,
            submission_limit: $validated['submission_limit'] ?? $questionnaire->submission_limit,
            duplicate_submission_strategy: \Liangjin0228\Questionnaire\Enums\DuplicateSubmissionStrategy::tryFrom(
                $validated['duplicate_submission_strategy'] ?? $questionnaire->duplicate_submission_strategy ?? 'allow_multiple'
            ) ?? \Liangjin0228\Questionnaire\Enums\DuplicateSubmissionStrategy::ALLOW_MULTIPLE,
            questions: $questions,
        );
    }
}
