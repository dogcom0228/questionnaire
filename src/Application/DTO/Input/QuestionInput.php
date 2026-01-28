<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\DTO\Input;

final readonly class QuestionInput
{
    /**
     * @param  array<int|string, mixed>|null  $options
     * @param  array<string, mixed>|null  $settings
     */
    public function __construct(
        public string $type,
        public string $content,
        public int $order,
        public bool $isRequired = false,
        public ?array $options = null,
        public ?array $settings = null
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'],
            content: $data['content'],
            order: $data['order'] ?? 0,
            isRequired: $data['is_required'] ?? $data['isRequired'] ?? false,
            options: $data['options'] ?? null,
            settings: $data['settings'] ?? null
        );
    }
}
