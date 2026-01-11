<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Http\Controllers;

use Liangjin0228\Questionnaire\Contracts\Actions\AddQuestionActionInterface;
use Liangjin0228\Questionnaire\Contracts\Actions\DeleteQuestionActionInterface;
use Liangjin0228\Questionnaire\Http\Requests\StoreQuestionRequest;
use Liangjin0228\Questionnaire\Models\Questionnaire;

class QuestionController extends BaseController
{
    public function __construct(
        protected AddQuestionActionInterface $addQuestionAction,
        protected DeleteQuestionActionInterface $deleteQuestionAction
    ) {}

    public function store(StoreQuestionRequest $request, Questionnaire $questionnaire)
    {
        $this->authorizeUpdate($questionnaire);

        $this->addQuestionAction->execute($questionnaire, $request->toDto());

        return back()->with('success', 'Question added.');
    }

    public function destroy(Questionnaire $questionnaire, int $questionId)
    {
        $this->authorizeUpdate($questionnaire);

        $this->deleteQuestionAction->execute($questionnaire, $questionId);

        return back()->with('success', 'Question deleted.');
    }

    protected function authorizeUpdate(Questionnaire $questionnaire): void
    {
        if (! config('questionnaire.features.authorization', true)) {
            return;
        }

        if (! request()->user()?->can('update', $questionnaire)) {
            abort(403);
        }
    }
}
