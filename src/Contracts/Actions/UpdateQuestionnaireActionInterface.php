<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Actions;

use Liangjin0228\Questionnaire\DTOs\QuestionnaireData;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;

/**
 * Contract for updating a questionnaire.
 */
interface UpdateQuestionnaireActionInterface
{
    /**
     * Update an existing questionnaire.
     *
     * @param  Questionnaire  $questionnaire  The questionnaire to update
     * @param  QuestionnaireData  $data  The updated data
     */
    public function execute(Questionnaire $questionnaire, QuestionnaireData $data): Questionnaire;
}
