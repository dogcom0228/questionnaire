<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Liangjin0228\Questionnaire\Application\Command\Questionnaire\CreateQuestionnaire\CreateQuestionnaireCommand;
use Liangjin0228\Questionnaire\Application\DTO\Input\QuestionnaireInput;
use Liangjin0228\Questionnaire\Contracts\Application\Bus\CommandBusInterface;
use Liangjin0228\Questionnaire\Domain\Question\Enums\QuestionType;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Entity\Question;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Enum\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionAdded;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnaireClosed;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnairePublished;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Infrastructure\ReadModel\QuestionnaireModel;

uses(RefreshDatabase::class);

test('can create questionnaire via command bus', function () {
    $commandBus = app(CommandBusInterface::class);

    $input = new QuestionnaireInput(
        title: 'Test Questionnaire',
        description: 'This is a test questionnaire',
        settings: null,
        questions: []
    );

    $questionnaireId = $commandBus->dispatch(new CreateQuestionnaireCommand(
        input: $input,
        userId: null
    ));

    expect($questionnaireId)->toBeString()->not->toBeEmpty();
})->skip('Skipped: CreateQuestionnaireHandler does not persist aggregate yet');

test('questionnaire creation records event in event store', function () {
    $id = QuestionnaireId::generate();
    $questionnaire = \Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire::create(
        id: $id,
        title: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle::fromString('Event Sourced Questionnaire'),
        slug: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSlug::fromString('event-sourced'),
        description: 'Testing event sourcing',
        dateRange: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\DateRange::create(null, null),
        settings: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSettings::default()
    );

    $questionnaire->persist();

    $this->assertDatabaseHas('stored_events', [
        'aggregate_uuid' => (string) $id->toUuid(),
    ]);

    $retrieved = \Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire::retrieve((string) $id->toUuid());
    expect($retrieved->title()->toString())->toBe('Event Sourced Questionnaire');
});

test('questionnaire status enum has correct values', function () {
    expect(QuestionnaireStatus::DRAFT->value)->toBe('draft')
        ->and(QuestionnaireStatus::PUBLISHED->value)->toBe('published')
        ->and(QuestionnaireStatus::CLOSED->value)->toBe('closed');
});

test('can publish questionnaire with questions', function () {
    $id = QuestionnaireId::generate();
    $questionnaire = \Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire::create(
        id: $id,
        title: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle::fromString('Publishable Questionnaire'),
        slug: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSlug::fromString('publishable'),
        description: null,
        dateRange: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\DateRange::create(null, null),
        settings: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSettings::default()
    );

    $question = Question::create(
        id: QuestionId::generate(),
        type: QuestionType::TEXT,
        content: 'What is your name?',
        description: null,
        options: [],
        required: true,
        order: 1,
        settings: []
    );
    $questionnaire->addQuestion($question);
    $questionnaire->publish();
    $questionnaire->persist();

    $this->assertDatabaseHas('stored_events', [
        'aggregate_uuid' => (string) $id->toUuid(),
        'event_class' => QuestionnairePublished::class,
    ]);

    $retrieved = \Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire::retrieve((string) $id->toUuid());
    expect($retrieved->status())->toBe(QuestionnaireStatus::PUBLISHED)
        ->and($retrieved->publishedAt())->not->toBeNull();
});

test('cannot publish questionnaire without questions', function () {
    $id = QuestionnaireId::generate();
    $questionnaire = \Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire::create(
        id: $id,
        title: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle::fromString('Empty Questionnaire'),
        slug: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSlug::fromString('empty'),
        description: null,
        dateRange: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\DateRange::create(null, null),
        settings: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSettings::default()
    );

    $questionnaire->publish();
})->throws(\Liangjin0228\Questionnaire\Domain\Questionnaire\Exception\InvalidQuestionnaireStateTransitionException::class);

test('can close published questionnaire', function () {
    $id = QuestionnaireId::generate();
    $questionnaire = \Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire::create(
        id: $id,
        title: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle::fromString('Closable Questionnaire'),
        slug: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSlug::fromString('closable'),
        description: null,
        dateRange: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\DateRange::create(null, null),
        settings: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSettings::default()
    );

    $question = Question::create(
        id: QuestionId::generate(),
        type: QuestionType::TEXT,
        content: 'Question',
        description: null,
        options: [],
        required: true,
        order: 1,
        settings: []
    );
    $questionnaire->addQuestion($question);
    $questionnaire->publish();
    $questionnaire->close();
    $questionnaire->persist();

    $this->assertDatabaseHas('stored_events', [
        'aggregate_uuid' => (string) $id->toUuid(),
        'event_class' => QuestionnaireClosed::class,
    ]);

    $retrieved = \Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire::retrieve((string) $id->toUuid());
    expect($retrieved->status())->toBe(QuestionnaireStatus::CLOSED)
        ->and($retrieved->closedAt())->not->toBeNull();
});

test('can add questions to questionnaire', function () {
    $id = QuestionnaireId::generate();
    $questionnaire = \Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire::create(
        id: $id,
        title: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle::fromString('Questionnaire with Questions'),
        slug: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSlug::fromString('with-questions'),
        description: null,
        dateRange: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\DateRange::create(null, null),
        settings: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSettings::default()
    );

    $question = Question::create(
        id: QuestionId::generate(),
        type: QuestionType::TEXT,
        content: 'What is your name?',
        description: null,
        options: [],
        required: true,
        order: 1,
        settings: []
    );
    $questionnaire->addQuestion($question);
    $questionnaire->persist();

    $this->assertDatabaseHas('stored_events', [
        'aggregate_uuid' => (string) $id->toUuid(),
        'event_class' => QuestionAdded::class,
    ]);

    $retrieved = \Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire::retrieve((string) $id->toUuid());
    expect($retrieved->questions())->toHaveCount(1)
        ->and($retrieved->hasQuestions())->toBeTrue();
});

test('validates read model table name for security', function () {
    config(['questionnaire.table_names.questionnaires' => 'invalid;DROP TABLE']);

    $questionnaireModel = new QuestionnaireModel;
    $questionnaireModel->getTable();
})->throws(\InvalidArgumentException::class, 'Invalid table name');
