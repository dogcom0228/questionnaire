<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Services;

use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Liangjin0228\Questionnaire\Contracts\Actions\SubmitResponseActionInterface;
use Liangjin0228\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Models\Response;
use Liangjin0228\Questionnaire\Submission\Pipes\CheckDuplicateSubmission;
use Liangjin0228\Questionnaire\Submission\Pipes\EnsureQuestionnaireIsOpen;
use Liangjin0228\Questionnaire\Submission\Pipes\FireResponseEvent;
use Liangjin0228\Questionnaire\Submission\Pipes\SaveResponse;
use Liangjin0228\Questionnaire\Submission\Pipes\ValidateSubmission;
use Liangjin0228\Questionnaire\Submission\SubmissionPassable;

class SubmitResponseAction implements SubmitResponseActionInterface
{
    public function __construct(
        protected Pipeline $pipeline
    ) {}

    /**
     * Submit a response to a questionnaire.
     *
     * @param  array<string, mixed>  $answers
     */
    public function execute(Questionnaire $questionnaire, array $answers, Request $request): Response
    {
        $passable = new SubmissionPassable($questionnaire, $request, $answers);

        $pipes = config('questionnaire.submission.pipes', [
            EnsureQuestionnaireIsOpen::class,
            CheckDuplicateSubmission::class,
            ValidateSubmission::class,
            SaveResponse::class,
            FireResponseEvent::class,
        ]);

        $this->pipeline
            ->send($passable)
            ->through($pipes)
            ->then(fn ($passable) => $passable);

        return $passable->response;
    }
}
