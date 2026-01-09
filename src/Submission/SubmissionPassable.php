<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Submission;

use Illuminate\Http\Request;
use Liangjin0228\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Models\Response;

class SubmissionPassable
{
    public function __construct(
        public readonly Questionnaire $questionnaire,
        public readonly Request $request,
        public array $answers,
        public ?Response $response = null
    ) {}

    public function setResponse(Response $response): self
    {
        $this->response = $response;

        return $this;
    }

    public function setAnswers(array $answers): self
    {
        $this->answers = $answers;

        return $this;
    }
}
