<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Liangjin0228\Questionnaire\Domain\Question\Enums\QuestionType;
use Liangjin0228\Questionnaire\Domain\Question\Models\Question;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Enums\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;

uses(RefreshDatabase::class, WithFaker::class);

test('can submit valid response', function () {
    $questionnaire = Questionnaire::create([
        'title' => 'Test Questionnaire',
        'status' => QuestionnaireStatus::PUBLISHED,
        'user_id' => 1,
        'is_accepting_responses' => true,
    ]);

    $question = Question::create([
        'questionnaire_id' => $questionnaire->id,
        'content' => 'What is your name?',
        'type' => QuestionType::TEXT,
        'required' => true,
        'order' => 1,
    ]);

    // Debug: Check if question was created and questionnaire has questions
    $questionnaire->refresh();
    expect($questionnaire->questions)->toHaveCount(1);
    expect($questionnaire->questions->first()->id)->toBe($question->id);

    $response = $this->postJson(route('questionnaire.api.submit', $questionnaire), [
        'answers' => [
            $question->id => 'John Doe',
        ],
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('questionnaire_responses', [
        'questionnaire_id' => $questionnaire->id,
    ]);

    $this->assertDatabaseHas('questionnaire_answers', [
        'question_id' => $question->id,
        'value' => 'John Doe',
    ]);
});

test('validation fails for required field', function () {
    $questionnaire = Questionnaire::create([
        'title' => 'Test Questionnaire',
        'status' => QuestionnaireStatus::PUBLISHED,
        'user_id' => 1,
        'is_accepting_responses' => true,
    ]);

    $question = Question::create([
        'questionnaire_id' => $questionnaire->id,
        'content' => 'What is your name?',
        'type' => QuestionType::TEXT,
        'required' => true,
        'order' => 1,
    ]);

    $response = $this->postJson(route('questionnaire.api.submit', $questionnaire), []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['answers.'.$question->id]);
});

test('cannot submit to closed questionnaire', function () {
    $questionnaire = Questionnaire::create([
        'title' => 'Test Questionnaire',
        'status' => QuestionnaireStatus::CLOSED,
        'user_id' => 1,
    ]);

    $question = Question::create([
        'questionnaire_id' => $questionnaire->id,
        'content' => 'What is your name?',
        'type' => QuestionType::TEXT,
        'required' => true,
        'order' => 1,
    ]);

    $response = $this->postJson(route('questionnaire.api.submit', $questionnaire), [
        'answers' => [
            $question->id => 'John Doe',
        ],
    ]);

    $response->assertStatus(403);

    $this->assertDatabaseMissing('questionnaire_responses', [
        'questionnaire_id' => $questionnaire->id,
    ]);
});
