<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Liangjin0228\Questionnaire\Contracts\QuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Contracts\QuestionTypeRegistryInterface;
use Liangjin0228\Questionnaire\Contracts\ResponseRepositoryInterface;
use Liangjin0228\Questionnaire\Http\Requests\StoreQuestionnaireRequest;
use Liangjin0228\Questionnaire\Http\Requests\SubmitResponseRequest;
use Liangjin0228\Questionnaire\Http\Requests\UpdateQuestionnaireRequest;
use Liangjin0228\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Services\CloseQuestionnaireAction;
use Liangjin0228\Questionnaire\Services\CreateQuestionnaireAction;
use Liangjin0228\Questionnaire\Services\PublishQuestionnaireAction;
use Liangjin0228\Questionnaire\Services\SubmitResponseAction;
use Liangjin0228\Questionnaire\Services\UpdateQuestionnaireAction;

class QuestionnaireApiController extends Controller
{
    public function __construct(
        protected QuestionnaireRepositoryInterface $questionnaireRepository,
        protected ResponseRepositoryInterface $responseRepository,
        protected QuestionTypeRegistryInterface $questionTypeRegistry,
        protected CreateQuestionnaireAction $createAction,
        protected UpdateQuestionnaireAction $updateAction,
        protected PublishQuestionnaireAction $publishAction,
        protected CloseQuestionnaireAction $closeAction,
        protected SubmitResponseAction $submitAction
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
     * Store a newly created questionnaire.
     */
    public function store(StoreQuestionnaireRequest $request): JsonResponse
    {
        $questionnaire = $this->createAction->execute(
            $request->validated(),
            $request->user()?->getKey()
        );

        return response()->json([
            'data' => $questionnaire,
            'message' => 'Questionnaire created successfully.',
        ], 201);
    }

    /**
     * Display the specified questionnaire.
     */
    public function show(Request $request, Questionnaire $questionnaire): JsonResponse
    {
        $this->authorize($request, 'view', $questionnaire);

        $questionnaire->load('questions');

        return response()->json([
            'data' => $questionnaire,
            'meta' => [
                'question_types' => $this->questionTypeRegistry->toArray(),
            ],
        ]);
    }

    /**
     * Update the specified questionnaire.
     */
    public function update(UpdateQuestionnaireRequest $request, Questionnaire $questionnaire): JsonResponse
    {
        $this->authorize($request, 'update', $questionnaire);
        $questionnaire = $this->updateAction->execute($questionnaire, $request->validated());

        return response()->json([
            'data' => $questionnaire,
            'message' => 'Questionnaire updated successfully.',
        ]);
    }

    /**
     * Remove the specified questionnaire.
     */
    public function destroy(Request $request, Questionnaire $questionnaire): JsonResponse
    {
        $this->authorize($request, 'delete', $questionnaire);

        $this->questionnaireRepository->delete($questionnaire);

        return response()->json([
            'message' => 'Questionnaire deleted successfully.',
        ]);
    }

    /**
     * Publish the questionnaire.
     */
    public function publish(Request $request, Questionnaire $questionnaire): JsonResponse
    {
        $this->authorize($request, 'publish', $questionnaire);

        try {
            $questionnaire = $this->publishAction->execute($questionnaire);

            return response()->json([
                'data' => $questionnaire,
                'message' => 'Questionnaire published successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Close the questionnaire.
     */
    public function close(Request $request, Questionnaire $questionnaire): JsonResponse
    {
        $this->authorize($request, 'close', $questionnaire);

        try {
            $questionnaire = $this->closeAction->execute($questionnaire);

            return response()->json([
                'data' => $questionnaire,
                'message' => 'Questionnaire closed successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
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
            'data' => $questionnaire,
            'meta' => [
                'question_types' => $this->questionTypeRegistry->toArray(),
            ],
        ]);
    }

    /**
     * Submit a response to the questionnaire.
     */
    public function submit(SubmitResponseRequest $request, Questionnaire $questionnaire): JsonResponse
    {
        try {
            $response = $this->submitAction->execute(
                $questionnaire,
                $request->validated(),
                $request
            );

            return response()->json([
                'data' => $response,
                'message' => 'Response submitted successfully.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
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
     * Get available question types.
     */
    public function questionTypes(): JsonResponse
    {
        return response()->json([
            'data' => $this->questionTypeRegistry->toArray(),
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
