<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Submission;

use Liangjin0228\Questionnaire\DTOs\SubmitResponseData;
use Liangjin0228\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Models\Response;

class SubmissionPassable
{
    public function __construct(
        public readonly Questionnaire $questionnaire,
        public readonly SubmitResponseData $data,
        public ?Response $response = null
    ) {}

    public function setResponse(Response $response): self
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get answers from the DTO.
     *
     * @return array<int|string, mixed>
     */
    public function getAnswers(): array
    {
        return $this->data->answers;
    }

    /**
     * Get the user ID from the DTO.
     */
    public function getUserId(): int|string|null
    {
        return $this->data->userId;
    }

    /**
     * Get the session ID from the DTO.
     */
    public function getSessionId(): ?string
    {
        return $this->data->sessionId;
    }

    /**
     * Get the IP address from the DTO.
     */
    public function getIpAddress(): ?string
    {
        return $this->data->ipAddress;
    }
}

