<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Liangjin0228\Questionnaire\Contracts\QuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Models\Questionnaire;

class EloquentQuestionnaireRepository implements QuestionnaireRepositoryInterface
{
    /**
     * Get the questionnaire model class.
     *
     * @return class-string<Questionnaire>
     */
    protected function getModelClass(): string
    {
        return config('questionnaire.models.questionnaire', Questionnaire::class);
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
    public function all(array $filters = []): Collection
    {
        $query = $this->newQuery()
            ->with(['user:id,name,email'])
            ->withCount('responses');

        $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * {@inheritdoc}
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->newQuery()
            ->with(['user:id,name,email'])
            ->withCount('responses');

        $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function find(int $id): ?Questionnaire
    {
        return $this->newQuery()->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findOrFail(int $id): Questionnaire
    {
        return $this->newQuery()->findOrFail($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findBySlug(string $slug): ?Questionnaire
    {
        return $this->newQuery()->where('slug', $slug)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Questionnaire
    {
        return $this->newQuery()->create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Questionnaire $questionnaire, array $data): bool
    {
        return $questionnaire->update($data);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Questionnaire $questionnaire): bool
    {
        return $questionnaire->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function getByStatus(string $status): Collection
    {
        return $this->newQuery()->where('status', $status)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getPublished(): Collection
    {
        return $this->newQuery()
            ->where('status', Questionnaire::STATUS_PUBLISHED)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getByUser(int $userId): Collection
    {
        return $this->newQuery()->where('user_id', $userId)->get();
    }

    /**
     * Apply filters to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array<string, mixed> $filters
     */
    protected function applyFilters($query, array $filters): void
    {
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['requires_auth'])) {
            $query->where('requires_auth', $filters['requires_auth']);
        }
    }
}
