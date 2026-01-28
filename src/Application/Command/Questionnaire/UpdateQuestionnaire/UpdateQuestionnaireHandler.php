<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Command\Questionnaire\UpdateQuestionnaire;

use Liangjin0228\Questionnaire\Contracts\Application\Bus\EventBusInterface;
use Liangjin0228\Questionnaire\Contracts\Application\CommandHandlerInterface;
use Liangjin0228\Questionnaire\Contracts\Application\CommandInterface;
use Liangjin0228\Questionnaire\Contracts\QuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\DateRange;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSettings;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSlug;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle;

final readonly class UpdateQuestionnaireHandler implements CommandHandlerInterface
{
    public function __construct(
        private QuestionnaireRepositoryInterface $repository,
        private EventBusInterface $eventBus
    ) {}

    public function handle(CommandInterface $command): mixed
    {
        assert($command instanceof UpdateQuestionnaireCommand);

        $id = QuestionnaireId::fromString($command->id);
        $title = QuestionnaireTitle::fromString($command->input->title);
        $slug = QuestionnaireSlug::fromTitle($title);
        $dateRange = DateRange::create(null, null);
        $settings = $command->input->settings
            ? QuestionnaireSettings::create(...$command->input->settings)
            : QuestionnaireSettings::default();

        return null;
    }
}
