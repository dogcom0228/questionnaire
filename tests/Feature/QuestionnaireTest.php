<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Enums\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Tests\TestCase;

class QuestionnaireTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_questionnaire(): void
    {
        $data = [
            'title' => 'Test Questionnaire',
            'description' => 'This is a test questionnaire',
            'status' => QuestionnaireStatus::DRAFT->value,
        ];

        $questionnaire = Questionnaire::create($data);

        $this->assertDatabaseHas('questionnaires', [
            'title' => 'Test Questionnaire',
            'status' => 'draft',
        ]);

        $this->assertEquals('Test Questionnaire', $questionnaire->title);
    }

    public function test_questionnaire_status_enum_has_correct_values(): void
    {
        $this->assertEquals('draft', QuestionnaireStatus::DRAFT->value);
        $this->assertEquals('published', QuestionnaireStatus::PUBLISHED->value);
        $this->assertEquals('closed', QuestionnaireStatus::CLOSED->value);
        $this->assertEquals('archived', QuestionnaireStatus::ARCHIVED->value);
    }

    public function test_questionnaire_is_active_when_published(): void
    {
        $questionnaire = Questionnaire::create([
            'title' => 'Active Questionnaire',
            'status' => QuestionnaireStatus::PUBLISHED->value,
            'published_at' => now(),
        ]);

        $this->assertTrue($questionnaire->is_active);
    }

    public function test_questionnaire_is_not_active_when_draft(): void
    {
        $questionnaire = Questionnaire::create([
            'title' => 'Draft Questionnaire',
            'status' => QuestionnaireStatus::DRAFT->value,
        ]);

        $this->assertFalse($questionnaire->is_active);
    }

    public function test_questionnaire_is_not_active_when_closed(): void
    {
        $questionnaire = Questionnaire::create([
            'title' => 'Closed Questionnaire',
            'status' => QuestionnaireStatus::CLOSED->value,
            'closed_at' => now(),
        ]);

        $this->assertFalse($questionnaire->is_active);
    }

    public function test_can_add_questions_to_questionnaire(): void
    {
        $questionnaire = Questionnaire::create([
            'title' => 'Questionnaire with Questions',
            'status' => 'draft',
        ]);

        $question = $questionnaire->questions()->create([
            'type' => 'text',
            'content' => 'What is your name?',
            'required' => true,
            'order' => 1,
        ]);

        $this->assertCount(1, $questionnaire->questions);
        $this->assertEquals('What is your name?', $question->content);
    }

    public function test_questionnaire_soft_deletes(): void
    {
        $questionnaire = Questionnaire::create([
            'title' => 'To Be Deleted',
            'status' => 'draft',
        ]);

        $id = $questionnaire->id;

        $questionnaire->delete();

        $this->assertSoftDeleted('questionnaires', ['id' => $id]);
    }

    public function test_validates_table_name_for_security(): void
    {
        config(['questionnaire.table_names.questionnaires' => 'invalid;DROP TABLE']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid table name');

        $questionnaire = new Questionnaire;
        $questionnaire->getTable();
    }
}
