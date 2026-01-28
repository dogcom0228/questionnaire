<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Command\Questionnaire\CreateQuestionnaire;

use Liangjin0228\Questionnaire\Application\DTO\Input\QuestionnaireInput;
use Liangjin0228\Questionnaire\Contracts\Application\CommandInterface;

final readonly class CreateQuestionnaireCommand implements CommandInterface
{
    public function __construct(
        public QuestionnaireInput $input,
        public ?string $userId = null
    ) {}
}
