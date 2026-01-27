<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Liangjin0228\Questionnaire\Domain\Question\Enums\QuestionType;
use Liangjin0228\Questionnaire\Domain\Question\Models\Question;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Enums\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Tests\TestCase;

class SubmissionTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_can_submit_valid_response()
    {
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
    }

    public function test_validation_fails_for_required_field()
    {
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

        $response = $this->postJson(route('questionnaire.api.submit', $questionnaire), [
            // Missing required field
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['answers.'.$question->id]);
    }

    public function test_cannot_submit_to_closed_questionnaire()
    {
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
    }
}
