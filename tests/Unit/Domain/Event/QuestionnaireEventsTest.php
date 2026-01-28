<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Entity\Question;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionAdded;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnaireClosed;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnaireCreated;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnairePublished;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnaireUpdated;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionRemoved;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionUpdated;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\DateRange;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSettings;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSlug;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionOptions;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionText;

describe('QuestionnaireCreated Event', function () {
    test('can be instantiated with all required properties', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $title = QuestionnaireTitle::fromString('Employee Survey');
        $slug = QuestionnaireSlug::fromString('employee-survey');
        $description = 'Annual employee satisfaction survey';
        $dateRange = DateRange::create(
            CarbonImmutable::parse('2024-01-01'),
            CarbonImmutable::parse('2024-12-31')
        );
        $settings = QuestionnaireSettings::default();

        $event = new QuestionnaireCreated(
            $questionnaireId,
            $title,
            $slug,
            $description,
            $dateRange,
            $settings
        );

        expect($event->questionnaireId)->toBe($questionnaireId)
            ->and($event->title)->toBe($title)
            ->and($event->slug)->toBe($slug)
            ->and($event->description)->toBe($description)
            ->and($event->dateRange)->toBe($dateRange)
            ->and($event->settings)->toBe($settings);
    });

    test('sets aggregate root uuid from questionnaire id', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $title = QuestionnaireTitle::fromString('Test Survey');
        $slug = QuestionnaireSlug::fromString('test-survey');
        $dateRange = DateRange::unlimited();
        $settings = QuestionnaireSettings::default();

        $event = new QuestionnaireCreated(
            $questionnaireId,
            $title,
            $slug,
            null,
            $dateRange,
            $settings
        );

        expect($event->aggregateRootUuid())->toBe($questionnaireId->toString());
    });

    test('toArray returns correct structure', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $title = QuestionnaireTitle::fromString('Customer Feedback');
        $slug = QuestionnaireSlug::fromString('customer-feedback');
        $description = 'Customer feedback form';
        $dateRange = DateRange::create(
            CarbonImmutable::parse('2024-06-01'),
            CarbonImmutable::parse('2024-06-30')
        );
        $settings = QuestionnaireSettings::default();

        $event = new QuestionnaireCreated(
            $questionnaireId,
            $title,
            $slug,
            $description,
            $dateRange,
            $settings
        );

        $array = $event->toArray();

        expect($array)->toBeArray()
            ->and($array)->toHaveKey('questionnaire_id')
            ->and($array)->toHaveKey('title')
            ->and($array)->toHaveKey('slug')
            ->and($array)->toHaveKey('description')
            ->and($array)->toHaveKey('date_range')
            ->and($array)->toHaveKey('settings')
            ->and($array)->toHaveKey('occurred_at')
            ->and($array['questionnaire_id'])->toBe($questionnaireId->toString())
            ->and($array['title'])->toBe($title->value())
            ->and($array['slug'])->toBe($slug->value())
            ->and($array['description'])->toBe($description);
    });

    test('can be created with null description', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $title = QuestionnaireTitle::fromString('Survey');
        $slug = QuestionnaireSlug::fromString('survey');
        $dateRange = DateRange::unlimited();
        $settings = QuestionnaireSettings::default();

        $event = new QuestionnaireCreated(
            $questionnaireId,
            $title,
            $slug,
            null,
            $dateRange,
            $settings
        );

        expect($event->description)->toBeNull()
            ->and($event->toArray()['description'])->toBeNull();
    });

    test('can be created with custom occurred at timestamp', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $title = QuestionnaireTitle::fromString('Survey');
        $slug = QuestionnaireSlug::fromString('survey');
        $dateRange = DateRange::unlimited();
        $settings = QuestionnaireSettings::default();
        $occurredAt = CarbonImmutable::parse('2024-01-15 10:30:00');

        $event = new QuestionnaireCreated(
            $questionnaireId,
            $title,
            $slug,
            null,
            $dateRange,
            $settings,
            $occurredAt
        );

        expect($event->occurredAt())->toEqual($occurredAt);
    });
});

describe('QuestionnaireUpdated Event', function () {
    test('can be instantiated with all required properties', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $title = QuestionnaireTitle::fromString('Updated Survey');
        $slug = QuestionnaireSlug::fromString('updated-survey');
        $description = 'Updated description';
        $dateRange = DateRange::create(
            CarbonImmutable::parse('2024-02-01'),
            CarbonImmutable::parse('2024-11-30')
        );
        $settings = QuestionnaireSettings::default();

        $event = new QuestionnaireUpdated(
            $questionnaireId,
            $title,
            $slug,
            $description,
            $dateRange,
            $settings
        );

        expect($event->questionnaireId)->toBe($questionnaireId)
            ->and($event->title)->toBe($title)
            ->and($event->slug)->toBe($slug)
            ->and($event->description)->toBe($description)
            ->and($event->dateRange)->toBe($dateRange)
            ->and($event->settings)->toBe($settings);
    });

    test('sets aggregate root uuid from questionnaire id', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $title = QuestionnaireTitle::fromString('Survey');
        $slug = QuestionnaireSlug::fromString('survey');
        $dateRange = DateRange::unlimited();
        $settings = QuestionnaireSettings::default();

        $event = new QuestionnaireUpdated(
            $questionnaireId,
            $title,
            $slug,
            null,
            $dateRange,
            $settings
        );

        expect($event->aggregateRootUuid())->toBe($questionnaireId->toString());
    });

    test('toArray returns correct structure', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $title = QuestionnaireTitle::fromString('Modified Survey');
        $slug = QuestionnaireSlug::fromString('modified-survey');
        $description = 'Modified';
        $dateRange = DateRange::unlimited();
        $settings = QuestionnaireSettings::default();

        $event = new QuestionnaireUpdated(
            $questionnaireId,
            $title,
            $slug,
            $description,
            $dateRange,
            $settings
        );

        $array = $event->toArray();

        expect($array)->toBeArray()
            ->and($array)->toHaveKey('questionnaire_id')
            ->and($array)->toHaveKey('title')
            ->and($array)->toHaveKey('slug')
            ->and($array)->toHaveKey('description')
            ->and($array)->toHaveKey('date_range')
            ->and($array)->toHaveKey('settings')
            ->and($array)->toHaveKey('occurred_at')
            ->and($array['questionnaire_id'])->toBe($questionnaireId->toString())
            ->and($array['title'])->toBe($title->value())
            ->and($array['slug'])->toBe($slug->value());
    });
});

describe('QuestionnairePublished Event', function () {
    test('can be instantiated with required properties', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $publishedAt = CarbonImmutable::now();

        $event = new QuestionnairePublished($questionnaireId, $publishedAt);

        expect($event->questionnaireId)->toBe($questionnaireId)
            ->and($event->publishedAt)->toBe($publishedAt);
    });

    test('sets aggregate root uuid from questionnaire id', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $publishedAt = CarbonImmutable::now();

        $event = new QuestionnairePublished($questionnaireId, $publishedAt);

        expect($event->aggregateRootUuid())->toBe($questionnaireId->toString());
    });

    test('toArray returns correct structure', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $publishedAt = CarbonImmutable::parse('2024-03-15 14:30:00');

        $event = new QuestionnairePublished($questionnaireId, $publishedAt);

        $array = $event->toArray();

        expect($array)->toBeArray()
            ->and($array)->toHaveKey('questionnaire_id')
            ->and($array)->toHaveKey('published_at')
            ->and($array)->toHaveKey('occurred_at')
            ->and($array['questionnaire_id'])->toBe($questionnaireId->toString())
            ->and($array['published_at'])->toBe($publishedAt->toIso8601String());
    });

    test('can be created with custom occurred at timestamp', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $publishedAt = CarbonImmutable::parse('2024-03-15 14:30:00');
        $occurredAt = CarbonImmutable::parse('2024-03-15 14:30:05');

        $event = new QuestionnairePublished($questionnaireId, $publishedAt, $occurredAt);

        expect($event->occurredAt())->toEqual($occurredAt);
    });
});

describe('QuestionnaireClosed Event', function () {
    test('can be instantiated with required properties', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $closedAt = CarbonImmutable::now();

        $event = new QuestionnaireClosed($questionnaireId, $closedAt);

        expect($event->questionnaireId)->toBe($questionnaireId)
            ->and($event->closedAt)->toBe($closedAt);
    });

    test('sets aggregate root uuid from questionnaire id', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $closedAt = CarbonImmutable::now();

        $event = new QuestionnaireClosed($questionnaireId, $closedAt);

        expect($event->aggregateRootUuid())->toBe($questionnaireId->toString());
    });

    test('toArray returns correct structure', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $closedAt = CarbonImmutable::parse('2024-12-31 23:59:59');

        $event = new QuestionnaireClosed($questionnaireId, $closedAt);

        $array = $event->toArray();

        expect($array)->toBeArray()
            ->and($array)->toHaveKey('questionnaire_id')
            ->and($array)->toHaveKey('closed_at')
            ->and($array)->toHaveKey('occurred_at')
            ->and($array['questionnaire_id'])->toBe($questionnaireId->toString())
            ->and($array['closed_at'])->toBe($closedAt->toIso8601String());
    });

    test('can be created with custom occurred at timestamp', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $closedAt = CarbonImmutable::parse('2024-12-31 23:59:59');
        $occurredAt = CarbonImmutable::parse('2024-12-31 23:59:59');

        $event = new QuestionnaireClosed($questionnaireId, $closedAt, $occurredAt);

        expect($event->occurredAt())->toEqual($occurredAt);
    });
});

describe('QuestionAdded Event', function () {
    test('can be instantiated with required properties', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('What is your name?'),
            'text',
            QuestionOptions::fromArray([]),
            true,
            1,
            null,
            []
        );

        $event = new QuestionAdded($questionnaireId, $question);

        expect($event->questionnaireId)->toBe($questionnaireId)
            ->and($event->question)->toBe($question);
    });

    test('sets aggregate root uuid from questionnaire id', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Question text'),
            'text',
            QuestionOptions::fromArray([]),
            true,
            1,
            null,
            []
        );

        $event = new QuestionAdded($questionnaireId, $question);

        expect($event->aggregateRootUuid())->toBe($questionnaireId->toString());
    });

    test('toArray returns correct structure', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $questionId = QuestionId::fromString(testUuid());
        $question = Question::create(
            $questionId,
            QuestionText::fromString('Rate our service'),
            'radio',
            QuestionOptions::fromArray(['Excellent', 'Good', 'Fair', 'Poor']),
            true,
            1,
            'Please select one',
            ['show_na' => true]
        );

        $event = new QuestionAdded($questionnaireId, $question);

        $array = $event->toArray();

        expect($array)->toBeArray()
            ->and($array)->toHaveKey('questionnaire_id')
            ->and($array)->toHaveKey('question')
            ->and($array)->toHaveKey('occurred_at')
            ->and($array['questionnaire_id'])->toBe($questionnaireId->toString())
            ->and($array['question'])->toBeArray()
            ->and($array['question']['id'])->toBe($questionId->toString())
            ->and($array['question']['text'])->toBe('Rate our service')
            ->and($array['question']['type'])->toBe('radio')
            ->and($array['question']['required'])->toBeTrue()
            ->and($array['question']['order'])->toBe(1)
            ->and($array['question']['description'])->toBe('Please select one');
    });

    test('can be created with custom occurred at timestamp', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Question'),
            'text',
            QuestionOptions::fromArray([]),
            true,
            1,
            null,
            []
        );
        $occurredAt = CarbonImmutable::parse('2024-05-20 10:00:00');

        $event = new QuestionAdded($questionnaireId, $question, $occurredAt);

        expect($event->occurredAt())->toEqual($occurredAt);
    });
});

describe('QuestionUpdated Event', function () {
    test('can be instantiated with required properties', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Updated question text'),
            'textarea',
            QuestionOptions::fromArray([]),
            false,
            2,
            'Updated description',
            []
        );

        $event = new QuestionUpdated($questionnaireId, $question);

        expect($event->questionnaireId)->toBe($questionnaireId)
            ->and($event->question)->toBe($question);
    });

    test('sets aggregate root uuid from questionnaire id', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Question'),
            'text',
            QuestionOptions::fromArray([]),
            true,
            1,
            null,
            []
        );

        $event = new QuestionUpdated($questionnaireId, $question);

        expect($event->aggregateRootUuid())->toBe($questionnaireId->toString());
    });

    test('toArray returns correct structure', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $questionId = QuestionId::fromString(testUuid());
        $question = Question::create(
            $questionId,
            QuestionText::fromString('Modified question'),
            'checkbox',
            QuestionOptions::fromArray(['Option 1', 'Option 2', 'Option 3']),
            false,
            3,
            'Modified description',
            []
        );

        $event = new QuestionUpdated($questionnaireId, $question);

        $array = $event->toArray();

        expect($array)->toBeArray()
            ->and($array)->toHaveKey('questionnaire_id')
            ->and($array)->toHaveKey('question')
            ->and($array)->toHaveKey('occurred_at')
            ->and($array['questionnaire_id'])->toBe($questionnaireId->toString())
            ->and($array['question']['id'])->toBe($questionId->toString())
            ->and($array['question']['text'])->toBe('Modified question')
            ->and($array['question']['type'])->toBe('checkbox')
            ->and($array['question']['required'])->toBeFalse()
            ->and($array['question']['order'])->toBe(3);
    });
});

describe('QuestionRemoved Event', function () {
    test('can be instantiated with required properties', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $questionId = QuestionId::fromString(testUuid());

        $event = new QuestionRemoved($questionnaireId, $questionId);

        expect($event->questionnaireId)->toBe($questionnaireId)
            ->and($event->questionId)->toBe($questionId);
    });

    test('sets aggregate root uuid from questionnaire id', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $questionId = QuestionId::fromString(testUuid());

        $event = new QuestionRemoved($questionnaireId, $questionId);

        expect($event->aggregateRootUuid())->toBe($questionnaireId->toString());
    });

    test('toArray returns correct structure', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $questionId = QuestionId::fromString(testUuid());

        $event = new QuestionRemoved($questionnaireId, $questionId);

        $array = $event->toArray();

        expect($array)->toBeArray()
            ->and($array)->toHaveKey('questionnaire_id')
            ->and($array)->toHaveKey('question_id')
            ->and($array)->toHaveKey('occurred_at')
            ->and($array['questionnaire_id'])->toBe($questionnaireId->toString())
            ->and($array['question_id'])->toBe($questionId->toString());
    });

    test('can be created with custom occurred at timestamp', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $questionId = QuestionId::fromString(testUuid());
        $occurredAt = CarbonImmutable::parse('2024-08-10 16:45:00');

        $event = new QuestionRemoved($questionnaireId, $questionId, $occurredAt);

        expect($event->occurredAt())->toEqual($occurredAt);
    });
});
