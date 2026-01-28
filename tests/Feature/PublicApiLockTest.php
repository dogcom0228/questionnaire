<?php

declare(strict_types=1);

use Liangjin0228\Questionnaire\Domain\Questionnaire\Enums\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;

test('denies access when questionnaire is not published', function () {
    $questionnaire = Questionnaire::factory()->create([
        'status' => QuestionnaireStatus::DRAFT->value,
    ]);

    $response = $this->getJson(route('questionnaire.api.public', $questionnaire));

    $response->assertStatus(403)
        ->assertJson(['message' => 'This questionnaire is not accepting responses.']);
});

test('denies access when submission limit is reached', function () {
    $questionnaire = Questionnaire::factory()->create([
        'status' => QuestionnaireStatus::PUBLISHED->value,
        'submission_limit' => 2,
    ]);

    $questionnaire->responses()->create(['data' => []]);
    $questionnaire->responses()->create(['data' => []]);

    $response = $this->getJson(route('questionnaire.api.public', $questionnaire));

    $response->assertStatus(403)
        ->assertJson(['message' => 'This questionnaire is not accepting responses.']);
});

test('denies submission when limit is reached', function () {
    $questionnaire = Questionnaire::factory()->create([
        'status' => QuestionnaireStatus::PUBLISHED->value,
        'submission_limit' => 2,
    ]);

    $questionnaire->responses()->create(['data' => []]);
    $questionnaire->responses()->create(['data' => []]);

    $response = $this->postJson(route('questionnaire.api.submit', $questionnaire), [
        'answers' => [],
    ]);

    $response->assertStatus(403)
        ->assertJson(['message' => 'This questionnaire is not accepting responses.']);
});

test('allows access when limit is not reached', function () {
    $questionnaire = Questionnaire::factory()->create([
        'status' => QuestionnaireStatus::PUBLISHED->value,
        'submission_limit' => 2,
    ]);

    $questionnaire->responses()->create(['data' => []]);

    $response = $this->getJson(route('questionnaire.api.public', $questionnaire));

    $response->assertStatus(200);
});
