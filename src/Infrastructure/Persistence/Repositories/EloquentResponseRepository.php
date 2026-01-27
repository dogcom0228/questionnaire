<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Persistence\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Liangjin0228\Questionnaire\Contracts\ResponseRepositoryInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Domain\Response\Models\Response;

class EloquentResponseRepository implements ResponseRepositoryInterface
{
    /**
     * Get the response model class.
     *
     * @return class-string<Response>
     */
    protected function getModelClass(): string
    {
        return config('questionnaire.models.response', Response::class);
    }

    /**
     * Get a new query builder instance.
     */
    protected function newQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return app($this->getModelClass())->newQuery();
    }

    /**
     * {@inheritdoc}
     */
    public function getForQuestionnaire(Questionnaire $questionnaire): Collection
    {
        return $this->newQuery()
            ->where('questionnaire_id', $questionnaire->id)
            ->with(['answers.question'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function paginateForQuestionnaire(Questionnaire $questionnaire, int $perPage = 15): LengthAwarePaginator
    {
        return $this->newQuery()
            ->where('questionnaire_id', $questionnaire->id)
            ->with(['answers.question'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function find(int $id): ?Response
    {
        return $this->newQuery()->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findOrFail(int $id): Response
    {
        return $this->newQuery()->findOrFail($id);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Response
    {
        return $this->newQuery()->create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Response $response): bool
    {
        return $response->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function getByRespondent(string $respondentType, int|string $respondentId): Collection
    {
        return $this->newQuery()
            ->where('respondent_type', $respondentType)
            ->where('respondent_id', $respondentId)
            ->with(['questionnaire', 'answers'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function hasRespondentSubmitted(
        Questionnaire $questionnaire,
        string $respondentType,
        int|string $respondentId
    ): bool {
        return $this->newQuery()
            ->where('questionnaire_id', $questionnaire->id)
            ->where('respondent_type', $respondentType)
            ->where('respondent_id', $respondentId)
            ->exists();
    }

    /**
     * {@inheritdoc}
     */
    public function countForQuestionnaire(Questionnaire $questionnaire): int
    {
        return $this->newQuery()
            ->where('questionnaire_id', $questionnaire->id)
            ->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getStatistics(Questionnaire $questionnaire): array
    {
        $totalResponses = $this->countForQuestionnaire($questionnaire);

        $questionStats = [];
        foreach ($questionnaire->questions as $question) {
            $answerCounts = DB::table(config('questionnaire.table_names.answers', 'questionnaire_answers'))
                ->where('question_id', $question->id)
                ->select('value', DB::raw('COUNT(*) as count'))
                ->groupBy('value')
                ->get()
                ->pluck('count', 'value')
                ->toArray();

            $questionStats[$question->id] = [
                'question_id' => $question->id,
                'question_content' => $question->content,
                'question_type' => $question->type,
                'total_answers' => array_sum($answerCounts),
                'answer_distribution' => $answerCounts,
            ];
        }

        return [
            'total_responses' => $totalResponses,
            'question_statistics' => $questionStats,
            'first_response_at' => $this->newQuery()
                ->where('questionnaire_id', $questionnaire->id)
                ->min('created_at'),
            'last_response_at' => $this->newQuery()
                ->where('questionnaire_id', $questionnaire->id)
                ->max('created_at'),
        ];
    }
}
