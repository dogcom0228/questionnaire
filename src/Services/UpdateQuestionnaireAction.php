<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Services;

use Liangjin0228\Questionnaire\Contracts\QuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Events\QuestionnaireUpdated;
use Liangjin0228\Questionnaire\Models\Questionnaire;

class UpdateQuestionnaireAction
{
    public function __construct(
        protected QuestionnaireRepositoryInterface $repository
    ) {}

    /**
     * Update an existing questionnaire.
     *
     * @param Questionnaire $questionnaire
     * @param array<string, mixed> $data
     */
    public function execute(Questionnaire $questionnaire, array $data): Questionnaire
    {
        $updateData = $this->prepareData($data, $questionnaire);

        $this->repository->update($questionnaire, $updateData);

        // Handle questions if provided
        if (isset($data['questions'])) {
            $this->syncQuestions($questionnaire, $data['questions']);
        }

        $questionnaire->refresh();

        event(new QuestionnaireUpdated($questionnaire));

        return $questionnaire;
    }

    /**
     * Prepare the update data.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function prepareData(array $data, Questionnaire $questionnaire): array
    {
        $updateData = [];

        $allowedFields = [
            'title',
            'description',
            'slug',
            'settings',
            'starts_at',
            'ends_at',
            'requires_auth',
            'submission_limit',
            'duplicate_submission_strategy',
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        // Regenerate slug if title changed and slug not explicitly provided
        if (isset($data['title']) && !isset($data['slug'])) {
            $newSlug = \Illuminate\Support\Str::slug($data['title']);
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
            $slug = $originalSlug . '-' . $counter;
            $counter++;
            $existing = $this->repository->findBySlug($slug);
        }

        return $slug;
    }

    /**
     * Sync questions for the questionnaire.
     *
     * @param array<int, array<string, mixed>> $questions
     */
    protected function syncQuestions(Questionnaire $questionnaire, array $questions): void
    {
        $existingIds = $questionnaire->questions()->pluck('id')->toArray();
        $providedIds = [];

        foreach ($questions as $order => $questionData) {
            if (isset($questionData['id']) && in_array($questionData['id'], $existingIds)) {
                // Update existing question
                $questionnaire->questions()
                    ->where('id', $questionData['id'])
                    ->update([
                        'type' => $questionData['type'],
                        'content' => $questionData['content'],
                        'description' => $questionData['description'] ?? null,
                        'options' => $questionData['options'] ?? null,
                        'required' => $questionData['required'] ?? false,
                        'order' => $questionData['order'] ?? $order,
                        'settings' => $questionData['settings'] ?? [],
                    ]);
                $providedIds[] = $questionData['id'];
            } else {
                // Create new question
                $newQuestion = $questionnaire->questions()->create([
                    'type' => $questionData['type'],
                    'content' => $questionData['content'],
                    'description' => $questionData['description'] ?? null,
                    'options' => $questionData['options'] ?? null,
                    'required' => $questionData['required'] ?? false,
                    'order' => $questionData['order'] ?? $order,
                    'settings' => $questionData['settings'] ?? [],
                ]);
                $providedIds[] = $newQuestion->id;
            }
        }

        // Delete questions that are no longer in the list
        $toDelete = array_diff($existingIds, $providedIds);
        if (!empty($toDelete)) {
            $questionnaire->questions()->whereIn('id', $toDelete)->delete();
        }
    }
}
