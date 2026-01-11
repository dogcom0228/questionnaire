<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Liangjin0228\Questionnaire\Contracts\ValidationStrategyInterface;
use Liangjin0228\Questionnaire\Models\Questionnaire;

class SubmitResponseRequest extends FormRequest
{
    protected ?Questionnaire $questionnaire = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $questionnaire = $this->getQuestionnaire();

        if (! $questionnaire) {
            return false;
        }

        // Check if questionnaire requires authentication
        if ($questionnaire->requires_auth && ! $this->user()) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $questionnaire = $this->getQuestionnaire();

        if (! $questionnaire) {
            return [];
        }

        // Use the validation strategy to get rules
        $validationStrategy = app(ValidationStrategyInterface::class);

        return $validationStrategy->getRules($questionnaire);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $questionnaire = $this->getQuestionnaire();

        if (! $questionnaire) {
            return [];
        }

        $validationStrategy = app(ValidationStrategyInterface::class);

        return $validationStrategy->getMessages($questionnaire);
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $questionnaire = $this->getQuestionnaire();

        if (! $questionnaire) {
            return [];
        }

        $validationStrategy = app(ValidationStrategyInterface::class);

        return $validationStrategy->getAttributes($questionnaire);
    }

    /**
     * Get the questionnaire from the route.
     */
    protected function getQuestionnaire(): ?Questionnaire
    {
        if ($this->questionnaire !== null) {
            return $this->questionnaire;
        }

        $questionnaire = $this->route('questionnaire');

        if ($questionnaire instanceof Questionnaire) {
            $this->questionnaire = $questionnaire->load('questions');

            return $this->questionnaire;
        }

        return null;
    }

    /**
     * Convert validated data to SubmitResponseData DTO.
     */
    public function toDto(): \Liangjin0228\Questionnaire\DTOs\SubmitResponseData
    {
        return new \Liangjin0228\Questionnaire\DTOs\SubmitResponseData(
            answers: $this->validated('answers') ?? [],
            userId: $this->user()?->getKey(),
            sessionId: $this->session()->getId(),
            ipAddress: $this->ip(),
        );
    }
}
