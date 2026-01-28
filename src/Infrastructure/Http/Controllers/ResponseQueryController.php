<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Liangjin0228\Questionnaire\Contracts\ResponseRepositoryInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;

class ResponseQueryController extends Controller
{
    public function __construct(
        protected ResponseRepositoryInterface $responseRepository
    ) {}

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
