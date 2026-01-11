<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Actions;

use Liangjin0228\Questionnaire\DTOs\QuestionnaireData;
use Liangjin0228\Questionnaire\Models\Questionnaire;

/**
 * Contract for creating a questionnaire.
 *
 * Implementing this interface allows you to completely replace
 * the questionnaire creation logic while maintaining compatibility
 * with the rest of the package.
 */
interface CreateQuestionnaireActionInterface
{
    /**
     * Create a new questionnaire.
     *
     * @param  QuestionnaireData  $data  The questionnaire data DTO
     * @param  int|string|null  $userId  The owner's user ID
     */
    public function execute(QuestionnaireData $data, int|string|null $userId = null): Questionnaire;
}
