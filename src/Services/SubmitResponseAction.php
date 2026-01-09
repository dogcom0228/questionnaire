<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Liangjin0228\Questionnaire\Contracts\DuplicateSubmissionGuardInterface;
use Liangjin0228\Questionnaire\Contracts\QuestionTypeRegistryInterface;
use Liangjin0228\Questionnaire\Contracts\ResponseRepositoryInterface;
use Liangjin0228\Questionnaire\Contracts\ValidationStrategyInterface;
use Liangjin0228\Questionnaire\Events\ResponseSubmitted;
use Liangjin0228\Questionnaire\Exceptions\DuplicateSubmissionException;
use Liangjin0228\Questionnaire\Exceptions\QuestionnaireClosedException;
use Liangjin0228\Questionnaire\Exceptions\ValidationException;
use Liangjin0228\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Models\Response;

class SubmitResponseAction
{
    public function __construct(
        protected ResponseRepositoryInterface $responseRepository,
        protected ValidationStrategyInterface $validationStrategy,
        protected DuplicateSubmissionGuardInterface $duplicateGuard,
        protected QuestionTypeRegistryInterface $questionTypeRegistry
    ) {}

    /**
     * Submit a response to a questionnaire.
     *
     * @param Questionnaire $questionnaire
     * @param array<string, mixed> $answers
     * @param Request $request
     * @return Response
     *
     * @throws QuestionnaireClosedException
     * @throws DuplicateSubmissionException
     * @throws ValidationException
     */
    public function execute(Questionnaire $questionnaire, array $answers, Request $request): Response
    {
        // Check if questionnaire is accepting responses
        $this->validateQuestionnaireStatus($questionnaire);

        // Check for duplicate submission
        if (!$this->duplicateGuard->canSubmit($questionnaire, $request)) {
            throw new DuplicateSubmissionException(
                $this->duplicateGuard->getRejectionReason($questionnaire, $request)
                    ?? 'You have already submitted a response to this questionnaire.'
            );
        }

        // Validate the answers
        $validator = $this->validationStrategy->validate($questionnaire, $answers);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Transform answer values
        $transformedAnswers = $this->transformAnswers($questionnaire, $answers);

        // Create the response with transaction
        $response = DB::transaction(function () use ($questionnaire, $transformedAnswers, $request) {
            $response = $this->responseRepository->create([
                'questionnaire_id' => $questionnaire->id,
                'respondent_type' => $this->getRespondentType($request),
                'respondent_id' => $this->getRespondentId($request),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => $this->getMetadata($request),
            ]);

            // Create answers
            foreach ($transformedAnswers as $questionId => $value) {
                $response->answers()->create([
                    'question_id' => $questionId,
                    'value' => is_array($value) ? json_encode($value) : $value,
                ]);
            }

            return $response;
        });

        // Mark as submitted in the guard
        $this->duplicateGuard->markAsSubmitted($questionnaire, $request);

        // Fire event
        event(new ResponseSubmitted($response));

        return $response;
    }

    /**
     * Validate that the questionnaire is accepting responses.
     *
     * @throws QuestionnaireClosedException
     */
    protected function validateQuestionnaireStatus(Questionnaire $questionnaire): void
    {
        if ($questionnaire->status !== Questionnaire::STATUS_PUBLISHED) {
            throw new QuestionnaireClosedException(
                'This questionnaire is not currently accepting responses.'
            );
        }

        if ($questionnaire->starts_at && $questionnaire->starts_at->isFuture()) {
            throw new QuestionnaireClosedException(
                'This questionnaire has not started yet.'
            );
        }

        if ($questionnaire->ends_at && $questionnaire->ends_at->isPast()) {
            throw new QuestionnaireClosedException(
                'This questionnaire has ended.'
            );
        }

        if ($questionnaire->submission_limit !== null) {
            $responseCount = $questionnaire->responses()->count();
            if ($responseCount >= $questionnaire->submission_limit) {
                throw new QuestionnaireClosedException(
                    'This questionnaire has reached its submission limit.'
                );
            }
        }
    }

    /**
     * Transform answer values using question type handlers.
     *
     * @param array<string, mixed> $answers
     * @return array<int, mixed>
     */
    protected function transformAnswers(Questionnaire $questionnaire, array $answers): array
    {
        $transformed = [];

        foreach ($questionnaire->questions as $question) {
            $key = "question_{$question->id}";
            if (!isset($answers[$key])) {
                continue;
            }

            $questionType = $this->questionTypeRegistry->get($question->type);
            if ($questionType) {
                $transformed[$question->id] = $questionType->transformValue($answers[$key]);
            } else {
                $transformed[$question->id] = $answers[$key];
            }
        }

        return $transformed;
    }

    /**
     * Get the respondent type (if authenticated).
     */
    protected function getRespondentType(Request $request): ?string
    {
        if ($request->user()) {
            return get_class($request->user());
        }
        return null;
    }

    /**
     * Get the respondent ID (if authenticated).
     */
    protected function getRespondentId(Request $request): int|string|null
    {
        return $request->user()?->getKey();
    }

    /**
     * Get additional metadata from the request.
     *
     * @return array<string, mixed>
     */
    protected function getMetadata(Request $request): array
    {
        return [
            'submitted_at' => now()->toIso8601String(),
            'referrer' => $request->header('referer'),
        ];
    }
}
