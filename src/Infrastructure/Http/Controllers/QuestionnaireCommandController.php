<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Liangjin0228\Questionnaire\Contracts\Actions\CloseQuestionnaireActionInterface;
use Liangjin0228\Questionnaire\Contracts\Actions\CreateQuestionnaireActionInterface;
use Liangjin0228\Questionnaire\Contracts\Actions\PublishQuestionnaireActionInterface;
use Liangjin0228\Questionnaire\Contracts\Actions\UpdateQuestionnaireActionInterface;
use Liangjin0228\Questionnaire\Contracts\QuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Infrastructure\Http\Request\StoreQuestionnaireRequest;
use Liangjin0228\Questionnaire\Infrastructure\Http\Request\UpdateQuestionnaireRequest;
use Liangjin0228\Questionnaire\Infrastructure\Http\Resource\QuestionnaireResource;

class QuestionnaireCommandController extends Controller
{
    public function __construct(
        protected QuestionnaireRepositoryInterface $questionnaireRepository,
        protected CreateQuestionnaireActionInterface $createAction,
        protected UpdateQuestionnaireActionInterface $updateAction,
        protected PublishQuestionnaireActionInterface $publishAction,
        protected CloseQuestionnaireActionInterface $closeAction
    ) {}

    /**
     * Store a newly created questionnaire.
     */
    public function store(StoreQuestionnaireRequest $request): JsonResponse
    {
        $questionnaire = $this->createAction->execute(
            $request->toDto(),
            $request->user()?->getKey()
        );

        return response()->json([
            'data' => new QuestionnaireResource($questionnaire),
            'message' => 'Questionnaire created successfully.',
        ], 201);
    }

    /**
     * Update the specified questionnaire.
     */
    public function update(UpdateQuestionnaireRequest $request, Questionnaire $questionnaire): JsonResponse
    {
        $this->authorize($request, 'update', $questionnaire);
        $questionnaire = $this->updateAction->execute($questionnaire, $request->toDto());

        return response()->json([
            'data' => new QuestionnaireResource($questionnaire),
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
                'data' => new QuestionnaireResource($questionnaire),
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
                'data' => new QuestionnaireResource($questionnaire),
                'message' => 'Questionnaire closed successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
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
