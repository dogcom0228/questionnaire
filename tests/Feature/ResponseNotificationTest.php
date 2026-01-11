<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Tests\Feature;

use Illuminate\Support\Facades\Mail;
use Liangjin0228\Questionnaire\Mail\ResponseSubmittedNotification;
use Liangjin0228\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Services\SubmitResponseAction;
use Liangjin0228\Questionnaire\Tests\TestCase;

class ResponseNotificationTest extends TestCase
{
    public function test_sends_notification_email_when_feature_enabled(): void
    {
        config(['questionnaire.features.email_notifications' => true]);

        Mail::fake();

        $questionnaire = Questionnaire::create([
            'title' => 'Notify Me',
            'status' => Questionnaire::STATUS_PUBLISHED,
            'settings' => [
                'notification_emails' => ['admin@example.com'],
            ],
        ]);

        $question = $questionnaire->questions()->create([
            'type' => 'text',
            'content' => 'Q1',
            'required' => false,
        ]);

        $action = app(SubmitResponseAction::class);
        $request = request();

        $answers = [
            "question_{$question->id}" => 'Answer',
        ];

        $action->execute($questionnaire, $answers, $request);

        Mail::assertSent(ResponseSubmittedNotification::class);
    }
}
