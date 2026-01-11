<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Listeners;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Liangjin0228\Questionnaire\Events\ResponseSubmitted;
use Liangjin0228\Questionnaire\Mail\ResponseSubmittedNotification;

class SendResponseNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected Mailer $mailer
    ) {}

    /**
     * Handle the event.
     */
    public function handle(ResponseSubmitted $event): void
    {
        if (! config('questionnaire.features.email_notifications', false)) {
            return;
        }

        $response = $event->response;
        $questionnaire = $response->questionnaire;

        // Get notification recipients
        $recipients = $this->getRecipients($questionnaire);

        if (empty($recipients)) {
            return;
        }

        foreach ($recipients as $recipient) {
            $this->mailer->to($recipient)->send(
                new ResponseSubmittedNotification($response)
            );
        }
    }

    /**
     * Get the notification recipients for a questionnaire.
     *
     * @return array<string>
     */
    protected function getRecipients($questionnaire): array
    {
        $recipients = [];

        // Add questionnaire owner if they have email notifications enabled
        if ($questionnaire->user && ($questionnaire->settings['notify_owner'] ?? false)) {
            $recipients[] = $questionnaire->user->email;
        }

        // Add any additional recipients from settings
        if (! empty($questionnaire->settings['notification_emails'])) {
            $recipients = array_merge(
                $recipients,
                (array) $questionnaire->settings['notification_emails']
            );
        }

        return array_unique(array_filter($recipients));
    }
}
