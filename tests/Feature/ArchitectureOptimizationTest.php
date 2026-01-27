<?php

namespace Liangjin0228\Questionnaire\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Liangjin0228\Questionnaire\Domain\Question\Enums\QuestionType;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Enums\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Events\QuestionnaireCreated;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Events\QuestionnaireCreating;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\DTOs\QuestionData;
use Liangjin0228\Questionnaire\DTOs\QuestionnaireData;
use Liangjin0228\Questionnaire\Services\CreateQuestionnaireAction;
use Liangjin0228\Questionnaire\Services\DefaultValidationStrategy;
use Liangjin0228\Questionnaire\Tests\TestCase;

class ArchitectureOptimizationTest extends TestCase
{
    public function test_create_questionnaire_action_uses_dto_and_enum()
    {
        Event::fake([QuestionnaireCreating::class, QuestionnaireCreated::class]);

        $action = app(CreateQuestionnaireAction::class);

        $dto = new QuestionnaireData(
            title: 'DTO Test Questionnaire',
            status: QuestionnaireStatus::DRAFT,
            questions: [
                new QuestionData(
                    type: QuestionType::TEXT,
                    content: 'What is your name?',
                    order: 1
                ),
            ]
        );

        $questionnaire = $action->execute($dto);

        $this->assertInstanceOf(Questionnaire::class, $questionnaire);
        $this->assertEquals('DTO Test Questionnaire', $questionnaire->title);
        $this->assertEquals('draft', $questionnaire->status);
        $this->assertCount(1, $questionnaire->questions);
        $this->assertEquals('text', $questionnaire->questions->first()->type);

        Event::assertDispatched(QuestionnaireCreating::class);
        Event::assertDispatched(QuestionnaireCreated::class);
    }

    public function test_regex_validation_rule_integration()
    {
        $questionnaire = Questionnaire::create([
            'title' => 'Regex Test',
            'status' => 'published',
        ]);

        $question = $questionnaire->questions()->create([
            'type' => 'text',
            'content' => 'Enter digits only',
            'settings' => ['regex' => '/^\d+$/'],
        ]);

        $strategy = app(DefaultValidationStrategy::class);
        $rules = $strategy->getRules($questionnaire);

        // The strategy now returns rules keyed by question ID directly (as strings)
        // because it validates the raw answers array in the pipe.
        // The prefixing happens in the Request class for API validation.
        $this->assertArrayHasKey((string) $question->id, $rules);
        $this->assertContains('regex:/^\d+$/', $rules[(string) $question->id]);
    }
}
