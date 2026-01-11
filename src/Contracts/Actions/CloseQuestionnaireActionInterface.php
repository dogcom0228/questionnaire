<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Actions;

use Liangjin0228\Questionnaire\Exceptions\QuestionnaireException;
use Liangjin0228\Questionnaire\Models\Questionnaire;

/**
 * Contract for closing a questionnaire.
 */
interface CloseQuestionnaireActionInterface
{
    /**
     * Close a questionnaire.
     *
     * @throws QuestionnaireException
     */
    public function execute(Questionnaire $questionnaire): Questionnaire;
}
