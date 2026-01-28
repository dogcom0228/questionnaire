<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Response\Guard;

use Liangjin0228\Questionnaire\Contracts\DuplicateSubmissionGuardInterface;
use Liangjin0228\Questionnaire\DTOs\SubmitResponseData;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Domain\Response\Models\Response;

/**
 * Allows only one submission per IP address.
 */
class OnePerIpGuard implements DuplicateSubmissionGuardInterface
{
    /**
     * {@inheritdoc}
     */
    public function canSubmit(Questionnaire $questionnaire, SubmitResponseData $data): bool
    {
        $ip = $data->ipAddress;

        if (! $ip) {
            return true;
        }

        $responseModel = config('questionnaire.models.response', Response::class);

        return ! app($responseModel)
            ->where('questionnaire_id', $questionnaire->id)
            ->where('ip_address', $ip)
            ->exists();
    }

    /**
     * {@inheritdoc}
     */
    public function getRejectionReason(Questionnaire $questionnaire, SubmitResponseData $data): ?string
    {
        if (! $this->canSubmit($questionnaire, $data)) {
            return 'A response has already been submitted from this IP address.';
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function markAsSubmitted(Questionnaire $questionnaire, SubmitResponseData $data): void
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
