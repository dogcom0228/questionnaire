<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Services;

use Illuminate\Support\Facades\DB;
use Liangjin0228\Questionnaire\Contracts\Actions\UpdateQuestionnaireActionInterface;
use Liangjin0228\Questionnaire\Contracts\QuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\DTOs\QuestionnaireData;
use Liangjin0228\Questionnaire\Events\QuestionnaireUpdated;
use Liangjin0228\Questionnaire\Models\Questionnaire;

class UpdateQuestionnaireAction implements UpdateQuestionnaireActionInterface
{
    public function __construct(
        protected QuestionnaireRepositoryInterface $repository
    ) {}

    /**
     * Update an existing questionnaire.
     */
    public function execute(Questionnaire $questionnaire, QuestionnaireData $data): Questionnaire
    {
        return DB::transaction(function () use ($questionnaire, $data) {
            $updateData = $this->prepareData($data, $questionnaire);

            $this->repository->update($questionnaire, $updateData);

            // Handle questions if provided
            if (! empty($data->questions)) {
                $this->syncQuestions($questionnaire, $data->questions);
            }

            $questionnaire->refresh();

            event(new QuestionnaireUpdated($questionnaire));

            return $questionnaire;
        });
    }

    /**
     * Prepare the update data.
     *
     * @return array<string, mixed>
     */
    protected function prepareData(QuestionnaireData $data, Questionnaire $questionnaire): array
    {
        $updateData = [
            'title' => $data->title,
            'description' => $data->description,
            'settings' => $data->settings,
            'starts_at' => $data->starts_at,
            'ends_at' => $data->ends_at,
            'requires_auth' => $data->requires_auth,
            'submission_limit' => $data->submission_limit,
            'duplicate_submission_strategy' => $data->duplicate_submission_strategy->value,
        ];

        // Handle slug: regenerate if title changed and slug not explicitly provided
        if ($data->slug !== null) {
            $updateData['slug'] = $data->slug;
        } else {
            $newSlug = \Illuminate\Support\Str::slug($data->title);
            if ($newSlug !== $questionnaire->slug) {
                $updateData['slug'] = $this->generateUniqueSlug($newSlug, $questionnaire->id);
            }
        }

        return $updateData;
    }

    /**
     * Generate a unique slug, excluding the current questionnaire.
     */
    protected function generateUniqueSlug(string $slug, int $excludeId): string
    {
        $originalSlug = $slug;
        $counter = 1;

        $existing = $this->repository->findBySlug($slug);
        while ($existing !== null && $existing->id !== $excludeId) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
            $existing = $this->repository->findBySlug($slug);
        }

        return $slug;
    }

    /**
     * Sync questions for the questionnaire.
     *
     * Handles create, update, and delete of questions based on the provided data.
     * Questions with an ID are updated; questions without are created.
     * Questions not in the provided list are deleted.
     *
     * @param  array<int, \Liangjin0228\Questionnaire\DTOs\QuestionData>  $questions
     */
    protected function syncQuestions(Questionnaire $questionnaire, array $questions): void
    {
        $existingIds = $questionnaire->questions()->pluck('id')->toArray();
        $providedIds = [];

        foreach ($questions as $order => $questionData) {
            $questionAttributes = [
                'type' => $questionData->type->value,
                'content' => $questionData->content,
                'description' => $questionData->description,
                'options' => $questionData->options,
                'required' => $questionData->required,
                'order' => $questionData->order ?: $order,
                'settings' => $questionData->settings ?? [],
            ];

            if ($questionData->id !== null && in_array($questionData->id, $existingIds, true)) {
                // Update existing question
                $questionnaire->questions()
                    ->where('id', $questionData->id)
                    ->update($questionAttributes);
                $providedIds[] = $questionData->id;
            } else {
                // Create new question
                $newQuestion = $questionnaire->questions()->create($questionAttributes);
                $providedIds[] = $newQuestion->id;
            }
        }

        // Delete questions that are no longer in the list
        $toDelete = array_diff($existingIds, $providedIds);
        if (! empty($toDelete)) {
            $questionnaire->questions()->whereIn('id', $toDelete)->delete();
        }
    }
}
