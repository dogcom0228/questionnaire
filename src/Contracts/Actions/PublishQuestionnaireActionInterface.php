<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Actions;

use Liangjin0228\Questionnaire\Exceptions\QuestionnaireException;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;

/**
 * Contract for publishing a questionnaire.
 */
interface PublishQuestionnaireActionInterface
{
    /**
     * Publish a questionnaire.
     *
     * @throws QuestionnaireException
     */
    public function execute(Questionnaire $questionnaire): Questionnaire;
}
