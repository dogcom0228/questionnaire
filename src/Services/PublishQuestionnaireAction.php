<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Services;

use Illuminate\Support\Facades\DB;
use Liangjin0228\Questionnaire\Contracts\Actions\PublishQuestionnaireActionInterface;
use Liangjin0228\Questionnaire\Contracts\QuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Enums\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Events\QuestionnairePublished;
use Liangjin0228\Questionnaire\Exceptions\QuestionnaireException;
use Liangjin0228\Questionnaire\Models\Questionnaire;

class PublishQuestionnaireAction implements PublishQuestionnaireActionInterface
{
    public function __construct(
        protected QuestionnaireRepositoryInterface $repository
    ) {}

    /**
     * Publish a questionnaire.
     *
     * @throws QuestionnaireException
     */
    public function execute(Questionnaire $questionnaire): Questionnaire
    {
        $this->validate($questionnaire);

        return DB::transaction(function () use ($questionnaire) {
            $this->repository->update($questionnaire, [
                'status' => QuestionnaireStatus::PUBLISHED->value,
                'published_at' => now(),
            ]);

            $questionnaire->refresh();

            event(new QuestionnairePublished($questionnaire));

            return $questionnaire;
        });
    }

    /**
     * Validate that the questionnaire can be published.
     *
     * @throws QuestionnaireException
     */
    protected function validate(Questionnaire $questionnaire): void
    {
        if ($questionnaire->status === QuestionnaireStatus::PUBLISHED->value) {
            throw QuestionnaireException::alreadyPublished();
        }

        if ($questionnaire->status === QuestionnaireStatus::CLOSED->value) {
            throw QuestionnaireException::cannotPublishClosed();
        }

        if ($questionnaire->questions()->count() === 0) {
            throw QuestionnaireException::noQuestions();
        }
    }
}
