<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Guards;

use Illuminate\Http\Request;
use Liangjin0228\Questionnaire\Contracts\DuplicateSubmissionGuardInterface;
use Liangjin0228\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Models\Response;

/**
 * Allows only one submission per IP address.
 */
class OnePerIpGuard implements DuplicateSubmissionGuardInterface
{
    /**
     * {@inheritdoc}
     */
    public function canSubmit(Questionnaire $questionnaire, Request $request): bool
    {
        $ip = $request->ip();

        if (!$ip) {
            return true;
        }

        $responseModel = config('questionnaire.models.response', Response::class);

        return !app($responseModel)
            ->where('questionnaire_id', $questionnaire->id)
            ->where('ip_address', $ip)
            ->exists();
    }

    /**
     * {@inheritdoc}
     */
    public function getRejectionReason(Questionnaire $questionnaire, Request $request): ?string
    {
        if (!$this->canSubmit($questionnaire, $request)) {
            return 'A response has already been submitted from this IP address.';
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function markAsSubmitted(Questionnaire $questionnaire, Request $request): void
    {
        // The IP is already recorded in the response
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return 'one_per_ip';
    }
}
