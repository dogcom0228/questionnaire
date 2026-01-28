<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\DTO\Output;

final readonly class QuestionOutput
{
    /**
     * @param  array<int|string, mixed>|null  $options
     * @param  array<string, mixed>|null  $settings
     */
    public function __construct(
        public string $id,
        public string $questionnaireId,
        public string $type,
        public string $content,
        public int $order,
        public bool $isRequired,
        public ?array $options,
        public ?array $settings
    ) {}
}
