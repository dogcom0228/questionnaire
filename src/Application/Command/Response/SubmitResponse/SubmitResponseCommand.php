<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Command\Response\SubmitResponse;

use Liangjin0228\Questionnaire\Application\DTO\Input\SubmitResponseInput;
use Liangjin0228\Questionnaire\Contracts\Application\CommandInterface;

final readonly class SubmitResponseCommand implements CommandInterface
{
    public function __construct(
        public string $questionnaireId,
        public SubmitResponseInput $input,
        public ?string $userId = null,
        public ?string $sessionId = null,
        public ?string $ipAddress = null
    ) {}
}
