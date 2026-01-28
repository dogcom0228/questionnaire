<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Entity\Question;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Enum\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionAdded;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnaireClosed;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnaireCreated;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnairePublished;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnaireUpdated;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionRemoved;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionUpdated;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Exception\InvalidQuestionnaireStateTransitionException;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\DateRange;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSettings;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSlug;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionOptions;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionText;

describe('Questionnaire Aggregate - Creation', function () {
    test('creates questionnaire and records QuestionnaireCreated event', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $title = QuestionnaireTitle::fromString('Employee Satisfaction Survey');
        $slug = QuestionnaireSlug::fromString('employee-satisfaction-2024');
        $description = 'Annual employee satisfaction survey';
        $dateRange = DateRange::create(
            CarbonImmutable::parse('2024-01-01'),
            CarbonImmutable::parse('2024-12-31')
        );
        $settings = QuestionnaireSettings::default();

        $questionnaire = Questionnaire::create($id, $title, $slug, $description, $dateRange, $settings);

        expect($questionnaire)->toBeInstanceOf(Questionnaire::class)
            ->and($questionnaire->recordedEvents())->toHaveCount(1)
            ->and($questionnaire->recordedEvents()[0])->toBeInstanceOf(QuestionnaireCreated::class);
    });

    test('applies QuestionnaireCreated event and sets initial state', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $title = QuestionnaireTitle::fromString('Customer Feedback Form');
        $slug = QuestionnaireSlug::fromString('customer-feedback');
        $description = 'We value your feedback';
        $dateRange = DateRange::create(
            CarbonImmutable::parse('2024-06-01'),
            CarbonImmutable::parse('2024-06-30')
        );
        $settings = QuestionnaireSettings::default();

        $questionnaire = Questionnaire::create($id, $title, $slug, $description, $dateRange, $settings);

        expect($questionnaire->id())->toEqual($id)
            ->and($questionnaire->title())->toEqual($title)
            ->and($questionnaire->slug())->toEqual($slug)
            ->and($questionnaire->description())->toBe($description)
            ->and($questionnaire->dateRange())->toEqual($dateRange)
            ->and($questionnaire->settings())->toEqual($settings)
            ->and($questionnaire->status())->toBe(QuestionnaireStatus::DRAFT)
            ->and($questionnaire->publishedAt())->toBeNull()
            ->and($questionnaire->closedAt())->toBeNull()
            ->and($questionnaire->questions())->toBeEmpty()
            ->and($questionnaire->hasQuestions())->toBeFalse()
            ->and($questionnaire->questionCount())->toBe(0);
    });

    test('creates questionnaire without description', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $title = QuestionnaireTitle::fromString('Simple Survey');
        $slug = QuestionnaireSlug::fromString('simple-survey');
        $dateRange = DateRange::create(
            CarbonImmutable::parse('2024-01-01'),
            CarbonImmutable::parse('2024-12-31')
        );
        $settings = QuestionnaireSettings::default();

        $questionnaire = Questionnaire::create($id, $title, $slug, null, $dateRange, $settings);

        expect($questionnaire->description())->toBeNull();
    });
});

describe('Questionnaire Aggregate - Update', function () {
    test('updates questionnaire and records QuestionnaireUpdated event', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $id,
            QuestionnaireTitle::fromString('Original Title'),
            QuestionnaireSlug::fromString('original-slug'),
            'Original description',
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

        $newTitle = QuestionnaireTitle::fromString('Updated Title');
        $newSlug = QuestionnaireSlug::fromString('updated-slug');
        $newDescription = 'Updated description';
        $newDateRange = DateRange::create(
            CarbonImmutable::parse('2024-02-01'),
            CarbonImmutable::parse('2024-11-30')
        );
        $newSettings = QuestionnaireSettings::default();

        $questionnaire->update($newTitle, $newSlug, $newDescription, $newDateRange, $newSettings);

        expect($questionnaire->recordedEvents())->toHaveCount(2)
            ->and($questionnaire->recordedEvents()[1])->toBeInstanceOf(QuestionnaireUpdated::class);
    });

    test('applies QuestionnaireUpdated event and updates state', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $id,
            QuestionnaireTitle::fromString('Original Title'),
            QuestionnaireSlug::fromString('original-slug'),
            'Original description',
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

        $newTitle = QuestionnaireTitle::fromString('Updated Title');
        $newSlug = QuestionnaireSlug::fromString('updated-slug');
        $newDescription = 'Updated description';
        $newDateRange = DateRange::create(
            CarbonImmutable::parse('2024-02-01'),
            CarbonImmutable::parse('2024-11-30')
        );
        $newSettings = QuestionnaireSettings::default();

        $questionnaire->update($newTitle, $newSlug, $newDescription, $newDateRange, $newSettings);

        expect($questionnaire->title())->toEqual($newTitle)
            ->and($questionnaire->slug())->toEqual($newSlug)
            ->and($questionnaire->description())->toBe($newDescription)
            ->and($questionnaire->dateRange())->toEqual($newDateRange)
            ->and($questionnaire->settings())->toEqual($newSettings)
            ->and($questionnaire->status())->toBe(QuestionnaireStatus::DRAFT);
    });
});

describe('Questionnaire Aggregate - Publish', function () {
    test('publishes questionnaire and records QuestionnairePublished event', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $id,
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

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
        $questionnaire->addQuestion($question);

        $questionnaire->publish();

        expect($questionnaire->recordedEvents())->toHaveCount(3)
            ->and($questionnaire->recordedEvents()[2])->toBeInstanceOf(QuestionnairePublished::class);
    });

    test('applies QuestionnairePublished event and updates status', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $id,
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Rate our service'),
            'radio',
            QuestionOptions::fromArray(['Excellent', 'Good', 'Fair', 'Poor']),
            true,
            1,
            null,
            []
        );
        $questionnaire->addQuestion($question);

        $questionnaire->publish();

        expect($questionnaire->status())->toBe(QuestionnaireStatus::PUBLISHED)
            ->and($questionnaire->publishedAt())->toBeInstanceOf(CarbonImmutable::class)
            ->and($questionnaire->publishedAt())->not->toBeNull();
    });

    test('cannot publish questionnaire without questions', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $id,
            QuestionnaireTitle::fromString('Empty Survey'),
            QuestionnaireSlug::fromString('empty-survey'),
            null,
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

        expect(fn () => $questionnaire->publish())
            ->toThrow(InvalidQuestionnaireStateTransitionException::class);
    });

    test('cannot publish questionnaire from invalid status', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $id,
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

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
        $questionnaire->addQuestion($question);
        $questionnaire->publish();
        $questionnaire->close();

        expect(fn () => $questionnaire->publish())
            ->toThrow(InvalidQuestionnaireStateTransitionException::class);
    });
});

describe('Questionnaire Aggregate - Close', function () {
    test('closes published questionnaire and records QuestionnaireClosed event', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $id,
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

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
        $questionnaire->addQuestion($question);
        $questionnaire->publish();

        $questionnaire->close();

        expect($questionnaire->recordedEvents())->toHaveCount(4)
            ->and($questionnaire->recordedEvents()[3])->toBeInstanceOf(QuestionnaireClosed::class);
    });

    test('applies QuestionnaireClosed event and updates status', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $id,
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

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
        $questionnaire->addQuestion($question);
        $questionnaire->publish();

        $questionnaire->close();

        expect($questionnaire->status())->toBe(QuestionnaireStatus::CLOSED)
            ->and($questionnaire->closedAt())->toBeInstanceOf(CarbonImmutable::class)
            ->and($questionnaire->closedAt())->not->toBeNull();
    });

    test('cannot close questionnaire from invalid status', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $id,
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

        expect(fn () => $questionnaire->close())
            ->toThrow(InvalidQuestionnaireStateTransitionException::class);
    });
});

describe('Questionnaire Aggregate - Add Question', function () {
    test('adds question to draft questionnaire and records QuestionAdded event', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $id,
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('What is your feedback?'),
            'textarea',
            QuestionOptions::fromArray([]),
            false,
            1,
            'Please be specific',
            []
        );

        $questionnaire->addQuestion($question);

        expect($questionnaire->recordedEvents())->toHaveCount(2)
            ->and($questionnaire->recordedEvents()[1])->toBeInstanceOf(QuestionAdded::class);
    });

    test('applies QuestionAdded event and adds question to collection', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $id,
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

        $question1 = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('First question'),
            'text',
            QuestionOptions::fromArray([]),
            true,
            1,
            null,
            []
        );

        $question2 = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Second question'),
            'radio',
            QuestionOptions::fromArray(['Yes', 'No']),
            true,
            2,
            null,
            []
        );

        $questionnaire->addQuestion($question1);
        $questionnaire->addQuestion($question2);

        expect($questionnaire->hasQuestions())->toBeTrue()
            ->and($questionnaire->questionCount())->toBe(2)
            ->and($questionnaire->questions())->toHaveKey($question1->id()->toString())
            ->and($questionnaire->questions())->toHaveKey($question2->id()->toString())
            ->and($questionnaire->questions()[$question1->id()->toString()])->toBe($question1)
            ->and($questionnaire->questions()[$question2->id()->toString()])->toBe($question2);
    });

    test('cannot add question to published questionnaire', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $id,
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

        $question1 = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('First question'),
            'text',
            QuestionOptions::fromArray([]),
            true,
            1,
            null,
            []
        );
        $questionnaire->addQuestion($question1);
        $questionnaire->publish();

        $question2 = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Second question'),
            'text',
            QuestionOptions::fromArray([]),
            true,
            2,
            null,
            []
        );

        expect(fn () => $questionnaire->addQuestion($question2))
            ->toThrow(InvalidQuestionnaireStateTransitionException::class);
    });
});

describe('Questionnaire Aggregate - Update Question', function () {
    test('updates existing question and records QuestionUpdated event', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $id,
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

        $questionId = QuestionId::fromString(testUuid());
        $originalQuestion = Question::create(
            $questionId,
            QuestionText::fromString('Original text'),
            'text',
            QuestionOptions::fromArray([]),
            true,
            1,
            null,
            []
        );
        $questionnaire->addQuestion($originalQuestion);

        $updatedQuestion = Question::create(
            $questionId,
            QuestionText::fromString('Updated text'),
            'textarea',
            QuestionOptions::fromArray([]),
            false,
            1,
            'Updated description',
            []
        );

        $questionnaire->updateQuestion($updatedQuestion);

        expect($questionnaire->recordedEvents())->toHaveCount(3)
            ->and($questionnaire->recordedEvents()[2])->toBeInstanceOf(QuestionUpdated::class);
    });

    test('applies QuestionUpdated event and replaces question in collection', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $id,
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

        $questionId = QuestionId::fromString(testUuid());
        $originalQuestion = Question::create(
            $questionId,
            QuestionText::fromString('Original text'),
            'text',
            QuestionOptions::fromArray([]),
            true,
            1,
            null,
            []
        );
        $questionnaire->addQuestion($originalQuestion);

        $updatedQuestion = Question::create(
            $questionId,
            QuestionText::fromString('Updated text'),
            'textarea',
            QuestionOptions::fromArray([]),
            false,
            1,
            'Updated description',
            []
        );
        $questionnaire->updateQuestion($updatedQuestion);

        expect($questionnaire->questions()[$questionId->toString()])->toBe($updatedQuestion)
            ->and($questionnaire->questions()[$questionId->toString()]->text())->toEqual(QuestionText::fromString('Updated text'));
    });

    test('cannot update non-existent question', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $id,
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

        $nonExistentQuestion = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Non-existent'),
            'text',
            QuestionOptions::fromArray([]),
            true,
            1,
            null,
            []
        );

        expect(fn () => $questionnaire->updateQuestion($nonExistentQuestion))
            ->toThrow(InvalidQuestionnaireStateTransitionException::class);
    });
});

describe('Questionnaire Aggregate - Remove Question', function () {
    test('removes question from draft questionnaire and records QuestionRemoved event', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $id,
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

        $questionId = QuestionId::fromString(testUuid());
        $question = Question::create(
            $questionId,
            QuestionText::fromString('Question to remove'),
            'text',
            QuestionOptions::fromArray([]),
            true,
            1,
            null,
            []
        );
        $questionnaire->addQuestion($question);

        $questionnaire->removeQuestion($questionId);

        expect($questionnaire->recordedEvents())->toHaveCount(3)
            ->and($questionnaire->recordedEvents()[2])->toBeInstanceOf(QuestionRemoved::class);
    });

    test('applies QuestionRemoved event and removes question from collection', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $id,
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

        $questionId1 = QuestionId::fromString(testUuid());
        $question1 = Question::create(
            $questionId1,
            QuestionText::fromString('First question'),
            'text',
            QuestionOptions::fromArray([]),
            true,
            1,
            null,
            []
        );

        $questionId2 = QuestionId::fromString(testUuid());
        $question2 = Question::create(
            $questionId2,
            QuestionText::fromString('Second question'),
            'text',
            QuestionOptions::fromArray([]),
            true,
            2,
            null,
            []
        );

        $questionnaire->addQuestion($question1);
        $questionnaire->addQuestion($question2);
        $questionnaire->removeQuestion($questionId1);

        expect($questionnaire->questionCount())->toBe(1)
            ->and($questionnaire->questions())->not->toHaveKey($questionId1->toString())
            ->and($questionnaire->questions())->toHaveKey($questionId2->toString());
    });

    test('cannot remove question from published questionnaire', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $id,
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

        $questionId = QuestionId::fromString(testUuid());
        $question = Question::create(
            $questionId,
            QuestionText::fromString('Question'),
            'text',
            QuestionOptions::fromArray([]),
            true,
            1,
            null,
            []
        );
        $questionnaire->addQuestion($question);
        $questionnaire->publish();

        expect(fn () => $questionnaire->removeQuestion($questionId))
            ->toThrow(InvalidQuestionnaireStateTransitionException::class);
    });

    test('cannot remove non-existent question', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $id,
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

        $nonExistentId = QuestionId::fromString(testUuid());

        expect(fn () => $questionnaire->removeQuestion($nonExistentId))
            ->toThrow(InvalidQuestionnaireStateTransitionException::class);
    });
});

describe('Questionnaire Aggregate - Getters', function () {
    test('getAggregateRootId returns UUID interface', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $id,
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

        expect($questionnaire->getAggregateRootId())->toBeInstanceOf(\Ramsey\Uuid\UuidInterface::class)
            ->and($questionnaire->getAggregateRootId()->toString())->toBe($id->toString());
    });

    test('isActive returns true when published and within date range', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $id,
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

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
        $questionnaire->addQuestion($question);
        $questionnaire->publish();

        $testDate = CarbonImmutable::parse('2024-06-15');
        expect($questionnaire->isActive($testDate))->toBeTrue();
    });

    test('isActive returns false when not published', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $id,
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

        $testDate = CarbonImmutable::parse('2024-06-15');
        expect($questionnaire->isActive($testDate))->toBeFalse();
    });

    test('isActive returns false when outside date range', function () {
        $id = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $id,
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

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
        $questionnaire->addQuestion($question);
        $questionnaire->publish();

        $testDate = CarbonImmutable::parse('2025-01-15');
        expect($questionnaire->isActive($testDate))->toBeFalse();
    });
});
