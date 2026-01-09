<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Liangjin0228\Questionnaire\Http\Resources\QuestionnaireResource;
use Liangjin0228\Questionnaire\Models\Questionnaire;

class ShowQuestionnaireResponse implements Responsable
{
    public function __construct(
        protected Questionnaire $questionnaire,
        protected array $statistics,
        protected array $questionTypes,
        protected string $component
    ) {}

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return $this->toJsonResponse();
        }

        return $this->toInertiaResponse();
    }

    protected function toJsonResponse(): JsonResponse
    {
        return response()->json([
            'data' => new QuestionnaireResource($this->questionnaire),
            'meta' => [
                'statistics' => $this->statistics,
                'question_types' => $this->questionTypes,
            ],
        ]);
    }

    protected function toInertiaResponse(): InertiaResponse
    {
        return Inertia::render($this->component, [
            'questionnaire' => $this->questionnaire,
            'statistics' => $this->statistics,
            'questionTypes' => $this->questionTypes,
        ]);
    }
}
