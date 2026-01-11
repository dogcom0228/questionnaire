<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Services;

use Liangjin0228\Questionnaire\Contracts\Actions\AddQuestionActionInterface;
use Liangjin0228\Questionnaire\DTOs\QuestionData;
use Liangjin0228\Questionnaire\Models\Question;
use Liangjin0228\Questionnaire\Models\Questionnaire;

class AddQuestionAction implements AddQuestionActionInterface
{
    public function execute(Questionnaire $questionnaire, QuestionData $data): Question
    {
        $questionAttributes = [
            'type' => $data->type->value,
            'content' => $data->content,
            'description' => $data->description,
            'options' => $data->options,
            'required' => $data->required,
            'order' => $data->order,
            'settings' => $data->settings ?? [],
        ];

        /** @var Question $question */
        $question = $questionnaire->questions()->create($questionAttributes);

        return $question;
    }
}
