<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Command\Questionnaire\PublishQuestionnaire;

use Liangjin0228\Questionnaire\Contracts\Application\Bus\EventBusInterface;
use Liangjin0228\Questionnaire\Contracts\Application\CommandHandlerInterface;
use Liangjin0228\Questionnaire\Contracts\Application\CommandInterface;
use Liangjin0228\Questionnaire\Contracts\QuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\DateRange;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;

final readonly class PublishQuestionnaireHandler implements CommandHandlerInterface
{
    public function __construct(
        private QuestionnaireRepositoryInterface $repository,
        private EventBusInterface $eventBus
    ) {}

    public function handle(CommandInterface $command): mixed
    {
        assert($command instanceof PublishQuestionnaireCommand);

        $id = QuestionnaireId::fromString($command->id);

        $dateRange = ($command->startsAt !== null || $command->endsAt !== null)
            ? DateRange::create($command->startsAt, $command->endsAt)
            : null;

        return null;
    }
}
