<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts\Actions;

use Illuminate\Http\Request;
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
     * @param  array<string, mixed>  $answers  The submitted answers
     * @param  Request  $request  The HTTP request
     */
    public function execute(Questionnaire $questionnaire, array $answers, Request $request): Response;
}
