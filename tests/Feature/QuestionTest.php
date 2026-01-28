<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Liangjin0228\Questionnaire\Domain\Question\Enums\QuestionType;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Entity\Question;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionAdded;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionOptions;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionText;
use Liangjin0228\Questionnaire\Infrastructure\ReadModel\QuestionModel;

uses(RefreshDatabase::class);

test('can create question via aggregate', function () {
    $questionnaireId = QuestionnaireId::generate();
    $questionnaire = \Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire::create(
        id: $questionnaireId,
        title: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle::fromString('Test Questionnaire'),
        slug: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSlug::fromString('test'),
        description: null,
        dateRange: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\DateRange::create(null, null),
        settings: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSettings::default()
    );

    $questionId = QuestionId::generate();
    $question = Question::create(
        id: $questionId,
        text: QuestionText::fromString('What is your favorite color?'),
        type: QuestionType::TEXT->value,
        options: QuestionOptions::empty(),
        required: true,
        order: 1,
        description: null,
        settings: []
    );

    $questionnaire->addQuestion($question);
    $questionnaire->persist();

    $this->assertDatabaseHas('stored_events', [
        'aggregate_uuid' => (string) $questionnaireId->toUuid(),
        'event_class' => QuestionAdded::class,
    ]);

    $retrieved = \Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire::retrieve((string) $questionnaireId->toUuid());
    $questions = $retrieved->questions();

    expect($questions)->toHaveCount(1);
    $retrievedQuestion = array_values($questions)[0];
    expect($retrievedQuestion->text()->value())->toBe('What is your favorite color?')
        ->and($retrievedQuestion->type())->toBe(QuestionType::TEXT->value);
});

test('question is part of questionnaire aggregate', function () {
    $questionnaireId = QuestionnaireId::generate();
    $questionnaire = \Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire::create(
        id: $questionnaireId,
        title: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle::fromString('Parent Questionnaire'),
        slug: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSlug::fromString('parent'),
        description: null,
        dateRange: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\DateRange::create(null, null),
        settings: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSettings::default()
    );

    $question = Question::create(
        id: QuestionId::generate(),
        text: QuestionText::fromString('Choose one'),
        type: QuestionType::RADIO->value,
        options: QuestionOptions::fromArray(['Option A', 'Option B']),
        required: false,
        order: 1,
        description: null,
        settings: []
    );

    $questionnaire->addQuestion($question);
    $questionnaire->persist();

    $retrieved = \Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire::retrieve((string) $questionnaireId->toUuid());

    expect($retrieved->questions())->toHaveCount(1);
    $retrievedQuestion = array_values($retrieved->questions())[0];
    expect($retrievedQuestion->text()->value())->toBe('Choose one');
});

test('question options are stored as array', function () {
    $questionnaireId = QuestionnaireId::generate();
    $questionnaire = \Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire::create(
        id: $questionnaireId,
        title: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle::fromString('Test'),
        slug: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSlug::fromString('test'),
        description: null,
        dateRange: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\DateRange::create(null, null),
        settings: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSettings::default()
    );

    $question = Question::create(
        id: QuestionId::generate(),
        text: QuestionText::fromString('Select all that apply'),
        type: QuestionType::CHECKBOX->value,
        options: QuestionOptions::fromArray(['Option 1', 'Option 2', 'Option 3']),
        required: false,
        order: 1,
        description: null,
        settings: []
    );

    $questionnaire->addQuestion($question);
    $questionnaire->persist();

    $retrieved = \Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire::retrieve((string) $questionnaireId->toUuid());
    $retrievedQuestion = array_values($retrieved->questions())[0];

    expect($retrievedQuestion->options()->value())->toBeArray()
        ->toHaveCount(3);
});

test('question settings are stored as array', function () {
    $questionnaireId = QuestionnaireId::generate();
    $questionnaire = \Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire::create(
        id: $questionnaireId,
        title: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle::fromString('Test'),
        slug: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSlug::fromString('test'),
        description: null,
        dateRange: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\DateRange::create(null, null),
        settings: \Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSettings::default()
    );

    $question = Question::create(
        id: QuestionId::generate(),
        text: QuestionText::fromString('Enter a number'),
        type: QuestionType::NUMBER->value,
        options: QuestionOptions::empty(),
        required: true,
        order: 1,
        description: null,
        settings: ['min' => 1, 'max' => 100]
    );

    $questionnaire->addQuestion($question);
    $questionnaire->persist();

    $retrieved = \Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire::retrieve((string) $questionnaireId->toUuid());
    $retrievedQuestion = array_values($retrieved->questions())[0];

    expect($retrievedQuestion->settings())->toBeArray()
        ->and($retrievedQuestion->settings()['min'])->toBe(1)
        ->and($retrievedQuestion->settings()['max'])->toBe(100);
});

test('validates question read model table name for security', function () {
    config(['questionnaire.table_names.questions' => 'invalid;DROP TABLE']);

    $questionModel = new QuestionModel;
    $questionModel->getTable();
})->throws(\InvalidArgumentException::class, 'Invalid table name');
