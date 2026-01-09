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
        if (!config('questionnaire.features.authorization', true)) {
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

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('questionnaires', 'slug')->ignore($questionnaireId),
            ],
            'settings' => ['nullable', 'array'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'requires_auth' => ['nullable', 'boolean'],
            'submission_limit' => ['nullable', 'integer', 'min:1'],
            'duplicate_submission_strategy' => ['nullable', 'string', 'in:allow_multiple,one_per_user,one_per_session,one_per_ip'],

            // Questions
            'questions' => ['nullable', 'array'],
            'questions.*.id' => ['nullable', 'integer', 'exists:questions,id'],
            'questions.*.type' => ['required_with:questions', 'string'],
            'questions.*.content' => ['required_with:questions', 'string'],
            'questions.*.description' => ['nullable', 'string'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.required' => ['nullable', 'boolean'],
            'questions.*.order' => ['nullable', 'integer', 'min:0'],
            'questions.*.settings' => ['nullable', 'array'],
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
}
