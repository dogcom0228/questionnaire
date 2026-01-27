<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Services;

use Illuminate\Support\Facades\DB;
use Liangjin0228\Questionnaire\Contracts\Actions\CloseQuestionnaireActionInterface;
use Liangjin0228\Questionnaire\Contracts\QuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Enums\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Events\QuestionnaireClosed;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Exceptions\QuestionnaireException;

class CloseQuestionnaireAction implements CloseQuestionnaireActionInterface
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
        if ($questionnaire->status === QuestionnaireStatus::CLOSED->value) {
            throw QuestionnaireException::alreadyClosed();
        }

        return DB::transaction(function () use ($questionnaire) {
            $this->repository->update($questionnaire, [
                'status' => QuestionnaireStatus::CLOSED->value,
                'closed_at' => now(),
            ]);

            $questionnaire->refresh();

            event(new QuestionnaireClosed($questionnaire));

            return $questionnaire;
        });
    }
}
