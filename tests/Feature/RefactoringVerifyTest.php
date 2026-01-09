<?php

namespace Liangjin0228\Questionnaire\Tests\Feature;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Liangjin0228\Questionnaire\Contracts\QuestionTypeRegistryInterface;
use Liangjin0228\Questionnaire\Events\ResponseSubmitted;
use Liangjin0228\Questionnaire\Http\Controllers\QuestionnaireController;
use Liangjin0228\Questionnaire\Http\Responses\ShowQuestionnaireResponse;
use Liangjin0228\Questionnaire\Managers\QuestionTypeManager;
use Liangjin0228\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\QuestionTypes\AbstractQuestionType;
use Liangjin0228\Questionnaire\Services\SubmitResponseAction;
use Liangjin0228\Questionnaire\Tests\TestCase;

class RefactoringVerifyTest extends TestCase
{
    use RefreshDatabase;

    public function test_question_type_manager_is_bound()
    {
        $registry = app(QuestionTypeRegistryInterface::class);
        $this->assertInstanceOf(QuestionTypeManager::class, $registry);
    }

    public function test_manager_can_register_custom_driver_via_config()
    {
        // Manager pattern uses 'extend' or explicit 'driver' calls.
        // Our adapter `register` method adds it to the list and uses extend.

        $registry = app(QuestionTypeRegistryInterface::class);
        $registry->register(CustomTestType::class);

        $this->assertTrue($registry->has('custom_test'));
        $this->assertInstanceOf(CustomTestType::class, $registry->get('custom_test'));
    }

    public function test_controller_show_returns_responsable()
    {
        $questionnaire = Questionnaire::create(['title' => 'Test', 'status' => 'published']);

        $controller = app(QuestionnaireController::class);

        // Mock authorization if needed, or bypass.
        // We might need to actAs a user or disable auth in config (TestCase does this).

        $response = $controller->show($questionnaire);

        $this->assertInstanceOf(Responsable::class, $response);
        $this->assertInstanceOf(ShowQuestionnaireResponse::class, $response);
    }

    public function test_submission_pipeline_works()
    {
        Event::fake([ResponseSubmitted::class]);

        $questionnaire = Questionnaire::create(['title' => 'Pipeline Test', 'status' => 'published']);
        $question = $questionnaire->questions()->create([
            'type' => 'text',
            'content' => 'Q1',
            'required' => false,
        ]);

        $action = app(SubmitResponseAction::class);
        $request = request();

        $answers = [
            "question_{$question->id}" => 'Answer 1',
        ];

        $response = $action->execute($questionnaire, $answers, $request);

        $this->assertDatabaseHas('questionnaire_responses', [
            'questionnaire_id' => $questionnaire->id,
        ]);

        Event::assertDispatched(ResponseSubmitted::class);
    }
}

class CustomTestType extends AbstractQuestionType
{
    public function getIdentifier(): string
    {
        return 'custom_test';
    }

    public function getName(): string
    {
        return 'Custom Test';
    }
}
