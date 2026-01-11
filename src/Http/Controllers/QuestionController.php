<?php

namespace Liangjin0228\Questionnaire\Http\Controllers;

use Illuminate\Http\Request;
use Liangjin0228\Questionnaire\Models\Question;
use Liangjin0228\Questionnaire\Models\Questionnaire;

class QuestionController extends BaseController
{
    protected function getQuestionModel()
    {
        return app(config('questionnaire.models.question', Question::class));
    }

    protected function getQuestionnaireModel()
    {
        return app(config('questionnaire.models.questionnaire', Questionnaire::class));
    }

    public function store(Request $request, $questionnaireId)
    {
        $questionnaire = $this->getQuestionnaireModel()->findOrFail($questionnaireId);

        if (config('questionnaire.features.authorization', true)) {
            if (! $request->user()?->can('update', $questionnaire)) {
                abort(403);
            }
        }

        $data = $request->validate([
            'content' => 'required|string',
            'type' => 'required|string|in:text,radio,checkbox,textarea,select,number,date',
            'options' => 'nullable|array',
            'required' => 'boolean',
            'order' => 'integer',
        ]);

        $questionnaire->questions()->create($data); // Note: this uses the relationship.
        // If Model is swapped, relationship needs to return correct instance.
        // Standard Eloquent relationship methods return instances of the related class defined in the relationship method.
        // So if user swaps `Question` model, they MUST also override `Questionnaire` model's `questions()` method to return the new class.
        // This is a known constraint of Eloquent.

        return back()->with('success', 'Question added.');
    }

    public function destroy($questionnaireId, $questionId)
    {
        $questionnaire = $this->getQuestionnaireModel()->findOrFail($questionnaireId);

        if (config('questionnaire.features.authorization', true)) {
            if (! request()->user()?->can('update', $questionnaire)) {
                abort(403);
            }
        }

        $question = $this->getQuestionModel()->where('questionnaire_id', $questionnaireId)->findOrFail($questionId);
        $question->delete();

        return back()->with('success', 'Question deleted.');
    }
}
