<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\DTO\Output;

final readonly class AnswerOutput
{
    /**
     * @param  array<string, mixed>  $value
     */
    public function __construct(
        public string $id,
        public string $questionId,
        public array $value,
    ) {}
}
