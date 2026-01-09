<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Liangjin0228\Questionnaire\Models\Question;
use Liangjin0228\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Tests\TestCase;

class QuestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_question(): void
    {
        $questionnaire = Questionnaire::create([
            'title' => 'Test Questionnaire',
            'status' => 'draft',
        ]);

        $question = $questionnaire->questions()->create([
            'type' => 'text',
            'content' => 'What is your favorite color?',
            'required' => true,
            'order' => 1,
        ]);

        $this->assertDatabaseHas('questions', [
            'content' => 'What is your favorite color?',
            'type' => 'text',
            'required' => true,
        ]);

        $this->assertEquals('text', $question->type);
    }

    public function test_question_belongs_to_questionnaire(): void
    {
        $questionnaire = Questionnaire::create([
            'title' => 'Parent Questionnaire',
            'status' => 'draft',
        ]);

        $question = $questionnaire->questions()->create([
            'type' => 'radio',
            'content' => 'Choose one',
            'options' => ['Option A', 'Option B'],
            'required' => false,
            'order' => 1,
        ]);

        $this->assertEquals($questionnaire->id, $question->questionnaire->id);
    }

    public function test_question_options_are_casted_to_array(): void
    {
        $questionnaire = Questionnaire::create([
            'title' => 'Test',
            'status' => 'draft',
        ]);

        $question = $questionnaire->questions()->create([
            'type' => 'checkbox',
            'content' => 'Select all that apply',
            'options' => ['Option 1', 'Option 2', 'Option 3'],
            'required' => false,
            'order' => 1,
        ]);

        $this->assertIsArray($question->options);
        $this->assertCount(3, $question->options);
    }

    public function test_question_settings_are_casted_to_array(): void
    {
        $questionnaire = Questionnaire::create([
            'title' => 'Test',
            'status' => 'draft',
        ]);

        $question = $questionnaire->questions()->create([
            'type' => 'number',
            'content' => 'Enter a number',
            'settings' => ['min' => 1, 'max' => 100],
            'required' => true,
            'order' => 1,
        ]);

        $this->assertIsArray($question->settings);
        $this->assertEquals(1, $question->settings['min']);
        $this->assertEquals(100, $question->settings['max']);
    }

    public function test_validates_table_name_for_security(): void
    {
        config(['questionnaire.table_names.questions' => 'invalid;DROP TABLE']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid table name');

        $question = new Question();
        $question->getTable();
    }
}
