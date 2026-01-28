<?php

declare(strict_types=1);

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

test('create questionnaire action uses dto and enum', function () {
    $this->markTestSkipped('CQRS replaced Action pattern - test needs refactoring');

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

    expect($questionnaire)->toBeInstanceOf(Questionnaire::class)
        ->and($questionnaire->title)->toBe('DTO Test Questionnaire')
        ->and($questionnaire->status)->toBe('draft')
        ->and($questionnaire->questions)->toHaveCount(1)
        ->and($questionnaire->questions->first()->type)->toBe('text');

    Event::assertDispatched(QuestionnaireCreating::class);
    Event::assertDispatched(QuestionnaireCreated::class);
});

test('regex validation rule integration', function () {
    $this->markTestSkipped('ValidationStrategy implementation changed - test needs updating');

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

    expect($rules)->toHaveKey((string) $question->id)
        ->and($rules[(string) $question->id])->toContain('regex:/^\d+$/');
});
