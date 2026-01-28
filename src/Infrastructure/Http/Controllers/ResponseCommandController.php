<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Liangjin0228\Questionnaire\Contracts\Actions\SubmitResponseActionInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Infrastructure\Http\Request\SubmitResponseRequest;

class ResponseCommandController extends Controller
{
    public function __construct(
        protected SubmitResponseActionInterface $submitAction
    ) {}

    /**
     * Submit a response to the questionnaire.
     */
    public function submit(SubmitResponseRequest $request, Questionnaire $questionnaire): JsonResponse
    {
        if (! $questionnaire->is_accepting_responses) {
            return response()->json([
                'message' => 'This questionnaire is not accepting responses.',
            ], 403);
        }

        try {
            $response = $this->submitAction->execute(
                $questionnaire,
                $request->toDto()
            );

            return response()->json([
                'data' => $response,
                'message' => 'Response submitted successfully.',
            ], 201);
        } catch (\Liangjin0228\Questionnaire\Exceptions\ValidationException $e) {
            throw $e;
        } catch (\Liangjin0228\Questionnaire\Exceptions\DuplicateSubmissionException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
