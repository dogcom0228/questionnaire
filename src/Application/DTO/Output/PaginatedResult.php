<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\DTO\Output;

/**
 * Generic paginated result DTO
 *
 * @template T
 */
final readonly class PaginatedResult
{
    /**
     * @param  array<T>  $items
     * @param  int  $total  Total number of items across all pages
     * @param  int  $page  Current page number (1-based)
     * @param  int  $perPage  Items per page
     * @param  int  $lastPage  Total number of pages
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $perPage,
        public int $lastPage
    ) {}

    /**
     * Create from Laravel paginator data
     *
     * @param  array<T>  $items
     * @return PaginatedResult<T>
     */
    public static function fromPaginatorData(
        array $items,
        int $total,
        int $currentPage,
        int $perPage
    ): self {
        return new self(
            items: $items,
            total: $total,
            page: $currentPage,
            perPage: $perPage,
            lastPage: (int) ceil($total / $perPage)
        );
    }
}
