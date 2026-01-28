<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\DTO\Output;

final readonly class ResponseOutput
{
    /**
     * @param  array<AnswerOutput>  $answers
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $id,
        public string $questionnaireId,
        public ?string $userId,
        public ?string $sessionId,
        public string $ipAddress,
        public string $userAgent,
        public array $answers,
        public array $metadata,
        public string $submittedAt,
    ) {}
}
