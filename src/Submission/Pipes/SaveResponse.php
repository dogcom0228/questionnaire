<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Submission\Pipes;

use Closure;
use Illuminate\Support\Facades\DB;
use Liangjin0228\Questionnaire\Contracts\QuestionTypeRegistryInterface;
use Liangjin0228\Questionnaire\Contracts\ResponseRepositoryInterface;
use Liangjin0228\Questionnaire\DTOs\SubmitResponseData;
use Liangjin0228\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Submission\SubmissionPassable;

class SaveResponse
{
    public function __construct(
        protected ResponseRepositoryInterface $responseRepository,
        protected QuestionTypeRegistryInterface $questionTypeRegistry
    ) {}

    public function handle(SubmissionPassable $passable, Closure $next)
    {
        $transformedAnswers = $this->transformAnswers($passable->questionnaire, $passable->getAnswers());

        // Transactional save
        $response = DB::transaction(function () use ($passable, $transformedAnswers) {
            $response = $this->responseRepository->create([
                'questionnaire_id' => $passable->questionnaire->id,
                'respondent_type' => $this->getRespondentType($passable->data),
                'respondent_id' => $passable->getUserId(),
                'ip_address' => $passable->getIpAddress(),
                'user_agent' => $passable->data->metadata['user_agent'] ?? null,
                'metadata' => $this->getMetadata($passable->data),
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

        $passable->setResponse($response);

        return $next($passable);
    }

    protected function transformAnswers(Questionnaire $questionnaire, array $answers): array
    {
        $transformed = [];

        foreach ($questionnaire->questions as $question) {
            $key = "question_{$question->id}";
            if (! isset($answers[$key])) {
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

    protected function getRespondentType(SubmitResponseData $data): ?string
    {
        if ($data->userId !== null) {
            // Return the configured user model class
            return config('questionnaire.models.user') ?? config('auth.providers.users.model') ?? 'App\\Models\\User';
        }

        return null;
    }

    protected function getMetadata(SubmitResponseData $data): array
    {
        return array_merge([
            'submitted_at' => now()->toIso8601String(),
            'session_id' => $data->sessionId,
        ], $data->metadata);
    }
}
