<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Submission\Pipes;

use Closure;
use Liangjin0228\Questionnaire\Domain\Response\Events\ResponseSubmitted;
use Liangjin0228\Questionnaire\Submission\SubmissionPassable;

class FireResponseEvent
{
    public function handle(SubmissionPassable $passable, Closure $next)
    {
        $response = $next($passable);

        if ($passable->response) {
            \Log::info('Response submitted successfully', [
                'response_id' => $passable->response->id,
                'questionnaire_id' => $passable->questionnaire->id,
            ]);

            event(new ResponseSubmitted($passable->response));
        }

        return $response;
    }
}
