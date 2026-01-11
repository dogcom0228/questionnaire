<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Submission\Pipes;

use Closure;
use Liangjin0228\Questionnaire\Contracts\ValidationStrategyInterface;
use Liangjin0228\Questionnaire\Exceptions\ValidationException;
use Liangjin0228\Questionnaire\Submission\SubmissionPassable;

class ValidateSubmission
{
    public function __construct(
        protected ValidationStrategyInterface $validationStrategy
    ) {}

    public function handle(SubmissionPassable $passable, Closure $next)
    {
        $validator = $this->validationStrategy->validate($passable->questionnaire, $passable->getAnswers());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $next($passable);
    }
}
