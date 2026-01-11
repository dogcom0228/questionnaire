<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Tests\Feature;

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Liangjin0228\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Tests\TestCase;

class QuestionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = new User;
        $this->user->forceFill([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->user->save();
    }

    public function test_can_add_question_via_controller(): void
    {
        $questionnaire = Questionnaire::create([
            'title' => 'Test Questionnaire',
            'status' => 'draft',
            'user_id' => $this->user->getKey(),
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('questionnaire.admin.questions.store', $questionnaire), [
                'type' => 'text',
                'content' => 'What is your name?',
                'required' => true,
                'order' => 1,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('questions', [
            'questionnaire_id' => $questionnaire->id,
            'content' => 'What is your name?',
            'type' => 'text',
        ]);
    }

    public function test_can_delete_question_via_controller(): void
    {
        $questionnaire = Questionnaire::create([
            'title' => 'Test Questionnaire',
            'status' => 'draft',
            'user_id' => $this->user->getKey(),
        ]);

        $question = $questionnaire->questions()->create([
            'type' => 'text',
            'content' => 'To be deleted',
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('questionnaire.admin.questions.destroy', ['questionnaire' => $questionnaire->id, 'questionId' => $question->id]));

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('questions', [
            'id' => $question->id,
        ]);
    }

    public function test_validation_prevents_invalid_question_data(): void
    {
        $questionnaire = Questionnaire::create([
            'title' => 'Test Questionnaire',
            'status' => 'draft',
            'user_id' => $this->user->getKey(),
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('questionnaire.admin.questions.store', $questionnaire), [
                'type' => 'invalid_type', // Invalid type
                'content' => '', // Required
            ]);

        $response->assertSessionHasErrors(['type', 'content']);
    }
}
