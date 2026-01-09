<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Liangjin0228\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Models\Response;

interface ResponseRepositoryInterface
{
    /**
     * Get all responses for a questionnaire.
     */
    public function getForQuestionnaire(Questionnaire $questionnaire): Collection;

    /**
     * Get paginated responses for a questionnaire.
     */
    public function paginateForQuestionnaire(Questionnaire $questionnaire, int $perPage = 15): LengthAwarePaginator;

    /**
     * Find response by ID.
     */
    public function find(int $id): ?Response;

    /**
     * Find response by ID or fail.
     */
    public function findOrFail(int $id): Response;

    /**
     * Create a new response.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Response;

    /**
     * Delete a response.
     */
    public function delete(Response $response): bool;

    /**
     * Get responses by respondent.
     *
     * @param string $respondentType
     * @param int|string $respondentId
     */
    public function getByRespondent(string $respondentType, int|string $respondentId): Collection;

    /**
     * Check if respondent has submitted response for questionnaire.
     *
     * @param string $respondentType
     * @param int|string $respondentId
     */
    public function hasRespondentSubmitted(
        Questionnaire $questionnaire,
        string $respondentType,
        int|string $respondentId
    ): bool;

    /**
     * Get response count for questionnaire.
     */
    public function countForQuestionnaire(Questionnaire $questionnaire): int;

    /**
     * Get statistics for questionnaire.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(Questionnaire $questionnaire): array;
}
