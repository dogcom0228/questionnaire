<?php

declare(strict_types=1);

use Liangjin0228\Questionnaire\Domain\Questionnaire\Enums\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;

test('getStatuses returns array with correct keys', function () {
    $statuses = Questionnaire::getStatuses();

    expect($statuses)
        ->toBeArray()
        ->toHaveKeys(['draft', 'published', 'closed']);
});

test('questionnaire has is_active attribute', function () {
    $questionnaire = new Questionnaire([
        'title' => 'Test',
        'status' => QuestionnaireStatus::PUBLISHED->value,
    ]);

    expect($questionnaire->toArray())
        ->toHaveKey('is_active');
});

test('questionnaire has is_accepting_responses attribute', function () {
    $questionnaire = new Questionnaire([
        'title' => 'Test',
        'status' => QuestionnaireStatus::PUBLISHED->value,
    ]);

    expect($questionnaire->toArray())
        ->toHaveKey('is_accepting_responses');
});

test('questionnaire has correct fillable attributes', function () {
    $fillable = (new Questionnaire)->getFillable();

    $expectedAttributes = [
        'title',
        'description',
        'slug',
        'status',
        'settings',
        'starts_at',
        'ends_at',
        'published_at',
        'closed_at',
        'user_id',
        'requires_auth',
        'submission_limit',
        'duplicate_submission_strategy',
    ];

    foreach ($expectedAttributes as $attribute) {
        expect($fillable)->toContain($attribute);
    }
});

test('questionnaire casts attributes correctly', function () {
    $questionnaire = new Questionnaire;
    $casts = $questionnaire->getCasts();

    expect($casts)
        ->toMatchArray([
            'settings' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'published_at' => 'datetime',
            'closed_at' => 'datetime',
            'requires_auth' => 'boolean',
            'submission_limit' => 'integer',
        ]);
});
