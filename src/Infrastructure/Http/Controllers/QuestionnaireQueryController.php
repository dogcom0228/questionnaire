<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Liangjin0228\Questionnaire\Contracts\QuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Contracts\QuestionTypeRegistryInterface;
use Liangjin0228\Questionnaire\Contracts\ResponseRepositoryInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Infrastructure\Http\Resource\QuestionnaireResource;
use Liangjin0228\Questionnaire\Infrastructure\Http\Resource\QuestionTypeResource;

class QuestionnaireQueryController extends Controller
{
    public function __construct(
        protected QuestionnaireRepositoryInterface $questionnaireRepository,
        protected QuestionTypeRegistryInterface $questionTypeRegistry,
        protected ResponseRepositoryInterface $responseRepository
    ) {}

    /**
     * Display a listing of questionnaires.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'search']);

        if (config('questionnaire.features.authorization', true) && $request->user()) {
            $filters['user_id'] = $request->user()->getKey();
        }

        $questionnaires = $this->questionnaireRepository->paginate(
            (int) $request->input('per_page', 15),
            $filters
        );

        return response()->json([
            'data' => $questionnaires,
            'meta' => [
                'statuses' => Questionnaire::getStatuses(),
            ],
        ]);
    }

    /**
     * Display the specified questionnaire.
     */
    public function show(Request $request, Questionnaire $questionnaire): JsonResponse
    {
        $this->authorize($request, 'view', $questionnaire);

        $questionnaire->load('questions');

        return response()->json([
            'data' => new QuestionnaireResource($questionnaire),
            'meta' => [
                'question_types' => QuestionTypeResource::collection($this->questionTypeRegistry->toArray()),
            ],
        ]);
    }

    /**
     * Get public questionnaire for filling.
     */
    public function public(Questionnaire $questionnaire): JsonResponse
    {
        if (! $questionnaire->is_accepting_responses) {
            return response()->json([
                'message' => 'This questionnaire is not accepting responses.',
            ], 403);
        }

        $questionnaire->load('questions');

        return response()->json([
            'data' => new QuestionnaireResource($questionnaire),
            'meta' => [
                'question_types' => QuestionTypeResource::collection($this->questionTypeRegistry->toArray()),
            ],
        ]);
    }

    /**
     * Get available question types.
     */
    public function questionTypes(): JsonResponse
    {
        return response()->json([
            'data' => QuestionTypeResource::collection($this->questionTypeRegistry->toArray()),
        ]);
    }

    /**
     * Get responses for a questionnaire.
     */
    public function responses(Request $request, Questionnaire $questionnaire): JsonResponse
    {
        $this->authorize($request, 'viewResponses', $questionnaire);

        $responses = $this->responseRepository->paginateForQuestionnaire(
            $questionnaire,
            (int) $request->input('per_page', 15)
        );

        return response()->json([
            'data' => $responses,
        ]);
    }

    /**
     * Get statistics for a questionnaire.
     */
    public function statistics(Request $request, Questionnaire $questionnaire): JsonResponse
    {
        $this->authorize($request, 'viewResponses', $questionnaire);

        return response()->json([
            'data' => $this->responseRepository->getStatistics($questionnaire),
        ]);
    }

    /**
     * Authorize an action.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function authorize(Request $request, string $ability, $model): void
    {
        if (! config('questionnaire.features.authorization', true)) {
            return;
        }

        if (! $request->user()?->can($ability, $model)) {
            abort(403);
        }
    }
}
