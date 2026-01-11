<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Services;

use Liangjin0228\Questionnaire\Contracts\Actions\DeleteQuestionActionInterface;
use Liangjin0228\Questionnaire\Models\Question;
use Liangjin0228\Questionnaire\Models\Questionnaire;

class DeleteQuestionAction implements DeleteQuestionActionInterface
{
    public function execute(Questionnaire $questionnaire, int $questionId): void
    {
        /** @var class-string<Question> $questionModel */
        $questionModel = config('questionnaire.models.question', Question::class);

        $question = app($questionModel)
            ->where('questionnaire_id', $questionnaire->getKey())
            ->findOrFail($questionId);

        $question->delete();
    }
}
