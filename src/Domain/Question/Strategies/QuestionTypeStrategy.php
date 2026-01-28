<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Question\Strategies;

use Liangjin0228\Questionnaire\Contracts\QuestionTypeRegistryInterface;
use Liangjin0228\Questionnaire\Domain\Question\Models\Question;

/**
 * @deprecated This strategy pattern will be refactored in later phases.
 * QuestionTypes have been moved to Domain\Questionnaire\QuestionType\.
 */
class QuestionTypeStrategy
{
    public function __construct(
        protected QuestionTypeRegistryInterface $registry
    ) {}

    /**
     * @return \Liangjin0228\Questionnaire\Contracts\QuestionTypeInterface|null
     */
    public function getHandler(Question $question)
    {
        return $this->registry->get($question->type);
    }
}
