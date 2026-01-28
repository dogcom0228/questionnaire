<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Mail;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Enums\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\DTOs\SubmitResponseData;
use Liangjin0228\Questionnaire\Mail\ResponseSubmittedNotification;
use Liangjin0228\Questionnaire\Services\SubmitResponseAction;

beforeEach(function () {
    config(['questionnaire.features.email_notifications' => true]);
});

test('sends notification email when feature enabled', function () {
    Mail::fake();

    $questionnaire = Questionnaire::create([
        'title' => 'Notify Me',
        'status' => QuestionnaireStatus::PUBLISHED->value,
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

    $answers = [
        "question_{$question->id}" => 'Answer',
    ];

    $data = new SubmitResponseData(
        answers: $answers,
        userId: null,
        sessionId: session()->getId(),
        ipAddress: request()->ip(),
    );

    $action->execute($questionnaire, $data);

    Mail::assertSent(ResponseSubmittedNotification::class);
});
