<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Command\Questionnaire\UpdateQuestionnaire;

use Liangjin0228\Questionnaire\Application\DTO\Input\QuestionnaireInput;
use Liangjin0228\Questionnaire\Contracts\Application\CommandInterface;

final readonly class UpdateQuestionnaireCommand implements CommandInterface
{
    public function __construct(
        public string $id,
        public QuestionnaireInput $input
    ) {}
}
