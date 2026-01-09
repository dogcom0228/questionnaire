<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Services;

use Liangjin0228\Questionnaire\Contracts\QuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Events\QuestionnaireClosed;
use Liangjin0228\Questionnaire\Exceptions\QuestionnaireException;
use Liangjin0228\Questionnaire\Models\Questionnaire;

class CloseQuestionnaireAction
{
    public function __construct(
        protected QuestionnaireRepositoryInterface $repository
    ) {}

    /**
     * Close a questionnaire.
     *
     * @throws QuestionnaireException
     */
    public function execute(Questionnaire $questionnaire): Questionnaire
    {
        if ($questionnaire->status === Questionnaire::STATUS_CLOSED) {
            throw QuestionnaireException::alreadyClosed();
        }

        $this->repository->update($questionnaire, [
            'status' => Questionnaire::STATUS_CLOSED,
            'closed_at' => now(),
        ]);

        $questionnaire->refresh();

        event(new QuestionnaireClosed($questionnaire));

        return $questionnaire;
    }
}
