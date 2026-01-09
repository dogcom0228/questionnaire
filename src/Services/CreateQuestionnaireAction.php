<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Services;

use Liangjin0228\Questionnaire\Contracts\QuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Events\QuestionnaireCreated;
use Liangjin0228\Questionnaire\Models\Questionnaire;

class CreateQuestionnaireAction
{
    public function __construct(
        protected QuestionnaireRepositoryInterface $repository
    ) {}

    /**
     * Create a new questionnaire.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $userId  The owner's user ID
     */
    public function execute(array $data, ?int $userId = null): Questionnaire
    {
        $questionnaireData = $this->prepareData($data, $userId);

        $questionnaire = $this->repository->create($questionnaireData);

        // Handle questions if provided
        if (! empty($data['questions'])) {
            $this->createQuestions($questionnaire, $data['questions']);
        }

        event(new QuestionnaireCreated($questionnaire));

        return $questionnaire;
    }

    /**
     * Prepare the questionnaire data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function prepareData(array $data, ?int $userId): array
    {
        $modelClass = config('questionnaire.models.questionnaire', Questionnaire::class);
        $status = defined("{$modelClass}::STATUS_DRAFT") ? $modelClass::STATUS_DRAFT : 'draft';

        return [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'slug' => $data['slug'] ?? $this->generateSlug($data['title']),
            'status' => $data['status'] ?? $status,
            'settings' => $data['settings'] ?? [],
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'user_id' => $userId,
            'requires_auth' => $data['requires_auth'] ?? false,
            'submission_limit' => $data['submission_limit'] ?? null,
            'duplicate_submission_strategy' => $data['duplicate_submission_strategy'] ?? 'allow_multiple',
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
     * @param  array<int, array<string, mixed>>  $questions
     */
    protected function createQuestions(Questionnaire $questionnaire, array $questions): void
    {
        foreach ($questions as $order => $questionData) {
            $questionnaire->questions()->create([
                'type' => $questionData['type'],
                'content' => $questionData['content'],
                'description' => $questionData['description'] ?? null,
                'options' => $questionData['options'] ?? null,
                'required' => $questionData['required'] ?? false,
                'order' => $questionData['order'] ?? $order,
                'settings' => $questionData['settings'] ?? [],
            ]);
        }
    }
}
