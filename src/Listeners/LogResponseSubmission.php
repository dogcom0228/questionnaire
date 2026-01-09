<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Listeners;

use Illuminate\Support\Facades\Log;
use Liangjin0228\Questionnaire\Events\ResponseSubmitted;

class LogResponseSubmission
{
    /**
     * Handle the event.
     */
    public function handle(ResponseSubmitted $event): void
    {
        if (! config('questionnaire.features.log_submissions', false)) {
            return;
        }

        $response = $event->response;
        $questionnaire = $response->questionnaire;

        Log::channel(config('questionnaire.log_channel', 'stack'))->info(
            'Questionnaire response submitted',
            [
                'questionnaire_id' => $questionnaire->id,
                'questionnaire_title' => $questionnaire->title,
                'response_id' => $response->id,
                'respondent_type' => $response->respondent_type,
                'respondent_id' => $response->respondent_id,
                'ip_address' => $response->ip_address,
            ]
        );
    }
}
