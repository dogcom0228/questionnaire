<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Services;

use Illuminate\Support\Facades\DB;
use Liangjin0228\Questionnaire\Contracts\Actions\CreateQuestionnaireActionInterface;
use Liangjin0228\Questionnaire\Contracts\QuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Events\QuestionnaireCreated;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Events\QuestionnaireCreating;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\DTOs\QuestionnaireData;

class CreateQuestionnaireAction implements CreateQuestionnaireActionInterface
{
    public function __construct(
        protected QuestionnaireRepositoryInterface $repository
    ) {}

    /**
     * Create a new questionnaire.
     */
    public function execute(QuestionnaireData $data, int|string|null $userId = null): Questionnaire
    {
        // Fire creating event
        event(new QuestionnaireCreating($data, $userId));

        return DB::transaction(function () use ($data, $userId) {
            $questionnaire = $this->repository->create($this->prepareData($data, $userId));

            // Handle questions if provided
            if (! empty($data->questions)) {
                $this->createQuestions($questionnaire, $data->questions);
            }

            event(new QuestionnaireCreated($questionnaire));

            return $questionnaire;
        });
    }

    /**
     * Prepare the questionnaire data.
     */
    protected function prepareData(QuestionnaireData $data, int|string|null $userId): array
    {
        return [
            'title' => $data->title,
            'description' => $data->description,
            'slug' => $data->slug ?? $this->generateSlug($data->title),
            'status' => $data->status->value,
            'settings' => $data->settings,
            'starts_at' => $data->starts_at,
            'ends_at' => $data->ends_at,
            'user_id' => $userId,
            'requires_auth' => $data->requires_auth,
            'submission_limit' => $data->submission_limit,
            'duplicate_submission_strategy' => $data->duplicate_submission_strategy->value,
        ];
    }

    /**
     * Generate a unique slug for the questionnaire.
     */
    protected function generateSlug(string $title): string
    {
        $slug = \Illuminate\Support\Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->repository->findBySlug($slug) !== null) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Create questions for the questionnaire.
     *
     * @param  array<\Liangjin0228\Questionnaire\DTOs\QuestionData>  $questions
     */
    protected function createQuestions(Questionnaire $questionnaire, array $questions): void
    {
        foreach ($questions as $questionData) {
            $questionnaire->questions()->create([
                'type' => $questionData->type instanceof \Liangjin0228\Questionnaire\Enums\QuestionType
                    ? $questionData->type->value
                    : $questionData->type,
                'content' => $questionData->content,
                'description' => $questionData->description,
                'options' => $questionData->options,
                'required' => $questionData->required,
                'order' => $questionData->order,
                'settings' => $questionData->settings,
            ]);
        }
    }
}
