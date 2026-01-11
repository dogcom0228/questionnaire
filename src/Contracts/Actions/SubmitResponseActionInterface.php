<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Actions;

use Liangjin0228\Questionnaire\DTOs\SubmitResponseData;
use Liangjin0228\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Models\Response;

/**
 * Contract for submitting a response to a questionnaire.
 */
interface SubmitResponseActionInterface
{
    /**
     * Submit a response to a questionnaire.
     *
     * @param  Questionnaire  $questionnaire  The questionnaire to submit to
     * @param  SubmitResponseData  $data  The submission data containing answers and metadata
     */
    public function execute(Questionnaire $questionnaire, SubmitResponseData $data): Response;
}
