<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;

interface QuestionnaireRepositoryInterface
{
    /**
     * Get all questionnaires with optional filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function all(array $filters = []): Collection;

    /**
     * Get paginated questionnaires.
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Find questionnaire by ID.
     */
    public function find(int $id): ?Questionnaire;

    /**
     * Find questionnaire by ID or fail.
     */
    public function findOrFail(int $id): Questionnaire;

    /**
     * Find questionnaire by slug.
     */
    public function findBySlug(string $slug): ?Questionnaire;

    /**
     * Create a new questionnaire.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Questionnaire;

    /**
     * Update an existing questionnaire.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Questionnaire $questionnaire, array $data): bool;

    /**
     * Delete a questionnaire.
     */
    public function delete(Questionnaire $questionnaire): bool;

    /**
     * Get questionnaires by status.
     */
    public function getByStatus(string $status): Collection;

    /**
     * Get published questionnaires (for public access).
     */
    public function getPublished(): Collection;

    /**
     * Get questionnaires for a specific user (owner).
     */
    public function getByUser(int $userId): Collection;
}
