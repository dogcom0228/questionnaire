<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Submission\Pipes;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Liangjin0228\Questionnaire\Contracts\QuestionTypeRegistryInterface;
use Liangjin0228\Questionnaire\Contracts\ResponseRepositoryInterface;
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
        $transformedAnswers = $this->transformAnswers($passable->questionnaire, $passable->answers);

        // Transactional save
        $response = DB::transaction(function () use ($passable, $transformedAnswers) {
            $response = $this->responseRepository->create([
                'questionnaire_id' => $passable->questionnaire->id,
                'respondent_type' => $this->getRespondentType($passable->request),
                'respondent_id' => $this->getRespondentId($passable->request),
                'ip_address' => $passable->request->ip(),
                'user_agent' => $passable->request->userAgent(),
                'metadata' => $this->getMetadata($passable->request),
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

    protected function getRespondentType(Request $request): ?string
    {
        if ($request->user()) {
            return get_class($request->user());
        }
        return null;
    }

    protected function getRespondentId(Request $request): int|string|null
    {
        return $request->user()?->getKey();
    }

    protected function getMetadata(Request $request): array
    {
        return [
            'submitted_at' => now()->toIso8601String(),
            'referrer' => $request->header('referer'),
        ];
    }
}
