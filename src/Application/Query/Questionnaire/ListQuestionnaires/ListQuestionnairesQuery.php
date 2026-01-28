<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Query\Questionnaire\ListQuestionnaires;

use Liangjin0228\Questionnaire\Contracts\Application\QueryInterface;

/**
 * @implements QueryInterface<PaginatedResult<QuestionnaireOutput>>
 */
final readonly class ListQuestionnairesQuery implements QueryInterface
{
    public function __construct(
        public ?string $userId = null,
        public ?string $status = null,
        public ?string $search = null,
        public int $page = 1,
        public int $perPage = 15,
        public string $sortBy = 'created_at',
        public string $sortDirection = 'desc'
    ) {}
}
