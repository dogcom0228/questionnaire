<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Actions;

use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;

interface DeleteQuestionActionInterface
{
    /**
     * Delete a question from a questionnaire.
     */
    public function execute(Questionnaire $questionnaire, int $questionId): void;
}
