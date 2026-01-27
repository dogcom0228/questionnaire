<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Actions;

use Liangjin0228\Questionnaire\DTOs\QuestionData;
use Liangjin0228\Questionnaire\Domain\Question\Models\Question;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;

interface AddQuestionActionInterface
{
    /**
     * Add a question to a questionnaire.
     */
    public function execute(Questionnaire $questionnaire, QuestionData $data): Question;
}
