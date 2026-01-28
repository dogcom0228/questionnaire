<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\DTO\Output;

final readonly class QuestionnaireOutput
{
    /**
     * @param  array<QuestionOutput>|null  $questions
     * @param  array<string, mixed>|null  $settings
     */
    public function __construct(
        public string $id,
        public string $title,
        public ?string $description,
        public string $slug,
        public string $status,
        public bool $isActive,
        public bool $isAcceptingResponses,
        public ?string $userId,
        public ?array $questions,
        public ?array $settings,
        public ?string $startsAt,
        public ?string $endsAt,
        public ?string $publishedAt,
        public ?string $closedAt,
        public string $createdAt,
        public string $updatedAt,
        public int $responseCount
    ) {}
}
