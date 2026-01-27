<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Question\Strategies;

use Liangjin0228\Questionnaire\Contracts\QuestionTypeRegistryInterface;
use Liangjin0228\Questionnaire\Domain\Question\Models\Question;

class QuestionTypeStrategy
{
    public function __construct(
        protected QuestionTypeRegistryInterface $registry
    ) {}

    /**
     * Get the handler for a specific question type.
     */
    public function getHandler(Question $question)
    {
        return $this->registry->get($question->type);
    }
}
