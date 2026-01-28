<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Command\Questionnaire\PublishQuestionnaire;

use DateTimeImmutable;
use Liangjin0228\Questionnaire\Contracts\Application\CommandInterface;

final readonly class PublishQuestionnaireCommand implements CommandInterface
{
    public function __construct(
        public string $id,
        public ?DateTimeImmutable $startsAt = null,
        public ?DateTimeImmutable $endsAt = null
    ) {}
}
