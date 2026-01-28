<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\DTO\Input;

final readonly class SubmitResponseInput
{
    /**
     * @param  array<string, mixed>  $answers
     */
    public function __construct(
        public array $answers,
        public ?string $email = null,
        public ?string $name = null
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            answers: $data['answers'] ?? [],
            email: $data['email'] ?? null,
            name: $data['name'] ?? null
        );
    }
}
