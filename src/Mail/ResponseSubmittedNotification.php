<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Liangjin0228\Questionnaire\Domain\Response\Models\Response;

class ResponseSubmittedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Response $response
    ) {}

    public function build(): self
    {
        $questionnaire = $this->response->questionnaire;

        return $this
            ->subject('New questionnaire response submitted')
            ->view('questionnaire::mail.response_submitted', [
                'response' => $this->response,
                'questionnaire' => $questionnaire,
            ]);
    }
}
