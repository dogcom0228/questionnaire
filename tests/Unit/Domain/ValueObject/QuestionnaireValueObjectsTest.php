<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Enum\DuplicateSubmissionStrategy;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Exception\InvalidDateRangeException;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Exception\InvalidQuestionnaireSlugException;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Exception\InvalidQuestionnaireTitleException;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Exception\InvalidQuestionTextException;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\DateRange;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSettings;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSlug;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionOptions;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionText;

describe('QuestionnaireTitle Value Object', function () {
    test('can be created from valid string', function () {
        $title = QuestionnaireTitle::fromString('Valid Title');

        expect($title->value())->toBe('Valid Title');
    });

    test('throws exception when title is too short', function () {
        QuestionnaireTitle::fromString('AB');
    })->throws(InvalidQuestionnaireTitleException::class);

    test('throws exception when title is too long', function () {
        QuestionnaireTitle::fromString(str_repeat('A', 256));
    })->throws(InvalidQuestionnaireTitleException::class);

    test('throws exception when title is empty after trimming', function () {
        QuestionnaireTitle::fromString('   ');
    })->throws(InvalidQuestionnaireTitleException::class);

    test('two titles with same value are equal', function () {
        $title1 = QuestionnaireTitle::fromString('Same Title');
        $title2 = QuestionnaireTitle::fromString('Same Title');

        expect($title1->equals($title2))->toBeTrue();
    });

    test('two titles with different values are not equal', function () {
        $title1 = QuestionnaireTitle::fromString('Title One');
        $title2 = QuestionnaireTitle::fromString('Title Two');

        expect($title1->equals($title2))->toBeFalse();
    });

    test('equals returns false for different value object types', function () {
        $title = QuestionnaireTitle::fromString('Title');
        $slug = QuestionnaireSlug::fromString('slug');

        expect($title->equals($slug))->toBeFalse();
    });

    test('value is trimmed', function () {
        $title = QuestionnaireTitle::fromString('  Title with spaces  ');

        expect($title->value())->toBe('  Title with spaces  ');
    });

    test('string representation returns value', function () {
        $title = QuestionnaireTitle::fromString('Test Title');

        expect((string) $title)->toBe('Test Title');
    });

    test('json serializes to value', function () {
        $title = QuestionnaireTitle::fromString('Test Title');

        expect($title->jsonSerialize())->toBe('Test Title');
    });
});

describe('QuestionnaireSlug Value Object', function () {
    test('can be created from valid string', function () {
        $slug = QuestionnaireSlug::fromString('valid-slug');

        expect($slug->value())->toBe('valid-slug');
    });

    test('can be created from title', function () {
        $title = QuestionnaireTitle::fromString('Test Title Here');
        $slug = QuestionnaireSlug::fromTitle($title);

        expect($slug->value())->toBe('test-title-here');
    });

    test('throws exception when slug is too short', function () {
        QuestionnaireSlug::fromString('ab');
    })->throws(InvalidQuestionnaireSlugException::class);

    test('throws exception when slug is too long', function () {
        QuestionnaireSlug::fromString(str_repeat('a', 256));
    })->throws(InvalidQuestionnaireSlugException::class);

    test('throws exception when slug has invalid format', function () {
        QuestionnaireSlug::fromString('Invalid Slug');
    })->throws(InvalidQuestionnaireSlugException::class);

    test('throws exception when slug has uppercase', function () {
        QuestionnaireSlug::fromString('Invalid-Slug');
    })->throws(InvalidQuestionnaireSlugException::class);

    test('throws exception when slug has spaces', function () {
        QuestionnaireSlug::fromString('invalid slug');
    })->throws(InvalidQuestionnaireSlugException::class);

    test('throws exception when slug has special characters', function () {
        QuestionnaireSlug::fromString('invalid_slug');
    })->throws(InvalidQuestionnaireSlugException::class);

    test('accepts slug with numbers', function () {
        $slug = QuestionnaireSlug::fromString('slug-123');

        expect($slug->value())->toBe('slug-123');
    });

    test('two slugs with same value are equal', function () {
        $slug1 = QuestionnaireSlug::fromString('same-slug');
        $slug2 = QuestionnaireSlug::fromString('same-slug');

        expect($slug1->equals($slug2))->toBeTrue();
    });

    test('two slugs with different values are not equal', function () {
        $slug1 = QuestionnaireSlug::fromString('slug-one');
        $slug2 = QuestionnaireSlug::fromString('slug-two');

        expect($slug1->equals($slug2))->toBeFalse();
    });

    test('equals returns false for different value object types', function () {
        $slug = QuestionnaireSlug::fromString('slug');
        $title = QuestionnaireTitle::fromString('Title');

        expect($slug->equals($title))->toBeFalse();
    });

    test('string representation returns value', function () {
        $slug = QuestionnaireSlug::fromString('test-slug');

        expect((string) $slug)->toBe('test-slug');
    });

    test('json serializes to value', function () {
        $slug = QuestionnaireSlug::fromString('test-slug');

        expect($slug->jsonSerialize())->toBe('test-slug');
    });
});

describe('QuestionnaireId Value Object', function () {
    test('can be generated', function () {
        $id = QuestionnaireId::generate();

        expect($id->value())->toBeString()
            ->and(strlen($id->value()))->toBe(36);
    });

    test('can be created from string', function () {
        $uuid = testUuid();
        $id = QuestionnaireId::fromString($uuid);

        expect($id->value())->toBe($uuid);
    });

    test('two ids with same uuid are equal', function () {
        $uuid = testUuid();
        $id1 = QuestionnaireId::fromString($uuid);
        $id2 = QuestionnaireId::fromString($uuid);

        expect($id1->equals($id2))->toBeTrue();
    });

    test('two ids with different uuids are not equal', function () {
        $id1 = QuestionnaireId::generate();
        $id2 = QuestionnaireId::generate();

        expect($id1->equals($id2))->toBeFalse();
    });

    test('equals returns false for different id types', function () {
        $uuid = testUuid();
        $questionnaireId = QuestionnaireId::fromString($uuid);
        $questionId = QuestionId::fromString($uuid);

        expect($questionnaireId->equals($questionId))->toBeFalse();
    });

    test('toString returns uuid string', function () {
        $uuid = testUuid();
        $id = QuestionnaireId::fromString($uuid);

        expect($id->toString())->toBe($uuid);
    });

    test('toUuid returns uuid interface', function () {
        $id = QuestionnaireId::generate();

        expect($id->toUuid())->toBeInstanceOf(\Ramsey\Uuid\UuidInterface::class);
    });

    test('string representation returns uuid', function () {
        $uuid = testUuid();
        $id = QuestionnaireId::fromString($uuid);

        expect((string) $id)->toBe($uuid);
    });

    test('json serializes to uuid string', function () {
        $uuid = testUuid();
        $id = QuestionnaireId::fromString($uuid);

        expect($id->jsonSerialize())->toBe($uuid);
    });
});

describe('QuestionText Value Object', function () {
    test('can be created from valid string', function () {
        $text = QuestionText::fromString('What is your name?');

        expect($text->value())->toBe('What is your name?');
    });

    test('trims whitespace from text', function () {
        $text = QuestionText::fromString('  Question text  ');

        expect($text->value())->toBe('Question text');
    });

    test('throws exception when text is empty', function () {
        QuestionText::fromString('');
    })->throws(InvalidQuestionTextException::class);

    test('throws exception when text is only whitespace', function () {
        QuestionText::fromString('   ');
    })->throws(InvalidQuestionTextException::class);

    test('throws exception when text is too long', function () {
        QuestionText::fromString(str_repeat('A', 1001));
    })->throws(InvalidQuestionTextException::class);

    test('accepts maximum length text', function () {
        $text = QuestionText::fromString(str_repeat('A', 1000));

        expect(mb_strlen($text->value()))->toBe(1000);
    });

    test('two texts with same value are equal', function () {
        $text1 = QuestionText::fromString('Same question?');
        $text2 = QuestionText::fromString('Same question?');

        expect($text1->equals($text2))->toBeTrue();
    });

    test('two texts with different values are not equal', function () {
        $text1 = QuestionText::fromString('Question one?');
        $text2 = QuestionText::fromString('Question two?');

        expect($text1->equals($text2))->toBeFalse();
    });

    test('equals returns false for different value object types', function () {
        $text = QuestionText::fromString('Question?');
        $title = QuestionnaireTitle::fromString('Title');

        expect($text->equals($title))->toBeFalse();
    });

    test('string representation returns trimmed value', function () {
        $text = QuestionText::fromString('  Question?  ');

        expect((string) $text)->toBe('Question?');
    });

    test('json serializes to trimmed value', function () {
        $text = QuestionText::fromString('  Question?  ');

        expect($text->jsonSerialize())->toBe('Question?');
    });
});

describe('QuestionId Value Object', function () {
    test('can be generated', function () {
        $id = QuestionId::generate();

        expect($id->value())->toBeString()
            ->and(strlen($id->value()))->toBe(36);
    });

    test('can be created from string', function () {
        $uuid = testUuid();
        $id = QuestionId::fromString($uuid);

        expect($id->value())->toBe($uuid);
    });

    test('two ids with same uuid are equal', function () {
        $uuid = testUuid();
        $id1 = QuestionId::fromString($uuid);
        $id2 = QuestionId::fromString($uuid);

        expect($id1->equals($id2))->toBeTrue();
    });

    test('two ids with different uuids are not equal', function () {
        $id1 = QuestionId::generate();
        $id2 = QuestionId::generate();

        expect($id1->equals($id2))->toBeFalse();
    });

    test('string representation returns uuid', function () {
        $uuid = testUuid();
        $id = QuestionId::fromString($uuid);

        expect((string) $id)->toBe($uuid);
    });

    test('json serializes to uuid string', function () {
        $uuid = testUuid();
        $id = QuestionId::fromString($uuid);

        expect($id->jsonSerialize())->toBe($uuid);
    });
});

describe('QuestionOptions Value Object', function () {
    test('can be created from array', function () {
        $options = QuestionOptions::fromArray(['Option 1', 'Option 2', 'Option 3']);

        expect($options->value())->toBe(['Option 1', 'Option 2', 'Option 3']);
    });

    test('can be created as empty', function () {
        $options = QuestionOptions::empty();

        expect($options->isEmpty())->toBeTrue()
            ->and($options->value())->toBe([]);
    });

    test('filters out empty options', function () {
        $options = QuestionOptions::fromArray(['Option 1', '', 'Option 2', '  ', 'Option 3']);

        expect($options->value())->toBe(['Option 1', 'Option 2', 'Option 3']);
    });

    test('trims option values', function () {
        $options = QuestionOptions::fromArray(['  Option 1  ', '  Option 2  ']);

        expect($options->value())->toBe(['Option 1', 'Option 2']);
    });

    test('normalizes array keys to sequential', function () {
        $options = QuestionOptions::fromArray([5 => 'Option 1', 10 => 'Option 2']);

        expect($options->value())->toBe(['Option 1', 'Option 2'])
            ->and(array_keys($options->value()))->toBe([0, 1]);
    });

    test('isEmpty returns true for empty options', function () {
        $options = QuestionOptions::empty();

        expect($options->isEmpty())->toBeTrue();
    });

    test('isEmpty returns false for non-empty options', function () {
        $options = QuestionOptions::fromArray(['Option 1']);

        expect($options->isEmpty())->toBeFalse();
    });

    test('count returns correct number of options', function () {
        $options = QuestionOptions::fromArray(['A', 'B', 'C']);

        expect($options->count())->toBe(3);
    });

    test('hasOption returns true when option exists', function () {
        $options = QuestionOptions::fromArray(['Option 1', 'Option 2']);

        expect($options->hasOption('Option 1'))->toBeTrue();
    });

    test('hasOption returns false when option does not exist', function () {
        $options = QuestionOptions::fromArray(['Option 1', 'Option 2']);

        expect($options->hasOption('Option 3'))->toBeFalse();
    });

    test('hasOption trims input before checking', function () {
        $options = QuestionOptions::fromArray(['Option 1']);

        expect($options->hasOption('  Option 1  '))->toBeTrue();
    });

    test('two option sets with same values are equal', function () {
        $options1 = QuestionOptions::fromArray(['A', 'B', 'C']);
        $options2 = QuestionOptions::fromArray(['A', 'B', 'C']);

        expect($options1->equals($options2))->toBeTrue();
    });

    test('two option sets with different values are not equal', function () {
        $options1 = QuestionOptions::fromArray(['A', 'B']);
        $options2 = QuestionOptions::fromArray(['A', 'C']);

        expect($options1->equals($options2))->toBeFalse();
    });

    test('two option sets with different order are not equal', function () {
        $options1 = QuestionOptions::fromArray(['A', 'B']);
        $options2 = QuestionOptions::fromArray(['B', 'A']);

        expect($options1->equals($options2))->toBeFalse();
    });

    test('equals returns false for different value object types', function () {
        $options = QuestionOptions::fromArray(['A', 'B']);
        $text = QuestionText::fromString('Text');

        expect($options->equals($text))->toBeFalse();
    });

    test('string representation returns comma-separated values', function () {
        $options = QuestionOptions::fromArray(['A', 'B', 'C']);

        expect((string) $options)->toBe('A, B, C');
    });

    test('string representation returns empty string for empty options', function () {
        $options = QuestionOptions::empty();

        expect((string) $options)->toBe('');
    });

    test('json serializes to array', function () {
        $options = QuestionOptions::fromArray(['A', 'B', 'C']);

        expect($options->jsonSerialize())->toBe(['A', 'B', 'C']);
    });
});

describe('DateRange Value Object', function () {
    test('can be created with start and end dates', function () {
        $start = CarbonImmutable::parse('2025-01-01');
        $end = CarbonImmutable::parse('2025-12-31');
        $range = DateRange::create($start, $end);

        expect($range->startDate())->toEqual($start)
            ->and($range->endDate())->toEqual($end);
    });

    test('can be created as unlimited', function () {
        $range = DateRange::unlimited();

        expect($range->startDate())->toBeNull()
            ->and($range->endDate())->toBeNull();
    });

    test('can be created with only start date', function () {
        $start = CarbonImmutable::parse('2025-01-01');
        $range = DateRange::from($start);

        expect($range->startDate())->toEqual($start)
            ->and($range->endDate())->toBeNull();
    });

    test('can be created with only end date', function () {
        $end = CarbonImmutable::parse('2025-12-31');
        $range = DateRange::until($end);

        expect($range->startDate())->toBeNull()
            ->and($range->endDate())->toEqual($end);
    });

    test('throws exception when start is after end', function () {
        $start = CarbonImmutable::parse('2025-12-31');
        $end = CarbonImmutable::parse('2025-01-01');

        DateRange::create($start, $end);
    })->throws(InvalidDateRangeException::class);

    test('allows start and end on same date', function () {
        $date = CarbonImmutable::parse('2025-01-01');
        $range = DateRange::create($date, $date);

        expect($range->startDate())->toEqual($date)
            ->and($range->endDate())->toEqual($date);
    });

    test('isActive returns true when within range', function () {
        $start = CarbonImmutable::parse('2025-01-01');
        $end = CarbonImmutable::parse('2025-12-31');
        $range = DateRange::create($start, $end);

        $now = CarbonImmutable::parse('2025-06-15');

        expect($range->isActive($now))->toBeTrue();
    });

    test('isActive returns false when before start', function () {
        $start = CarbonImmutable::parse('2025-01-01');
        $end = CarbonImmutable::parse('2025-12-31');
        $range = DateRange::create($start, $end);

        $now = CarbonImmutable::parse('2024-12-31');

        expect($range->isActive($now))->toBeFalse();
    });

    test('isActive returns false when after end', function () {
        $start = CarbonImmutable::parse('2025-01-01');
        $end = CarbonImmutable::parse('2025-12-31');
        $range = DateRange::create($start, $end);

        $now = CarbonImmutable::parse('2026-01-01');

        expect($range->isActive($now))->toBeFalse();
    });

    test('isActive returns true for unlimited range', function () {
        $range = DateRange::unlimited();

        expect($range->isActive())->toBeTrue();
    });

    test('isActive returns true when only start date and after it', function () {
        $start = CarbonImmutable::parse('2025-01-01');
        $range = DateRange::from($start);

        $now = CarbonImmutable::parse('2025-06-15');

        expect($range->isActive($now))->toBeTrue();
    });

    test('isActive returns false when only start date and before it', function () {
        $start = CarbonImmutable::parse('2025-01-01');
        $range = DateRange::from($start);

        $now = CarbonImmutable::parse('2024-12-31');

        expect($range->isActive($now))->toBeFalse();
    });

    test('isActive returns true when only end date and before it', function () {
        $end = CarbonImmutable::parse('2025-12-31');
        $range = DateRange::until($end);

        $now = CarbonImmutable::parse('2025-06-15');

        expect($range->isActive($now))->toBeTrue();
    });

    test('isActive returns false when only end date and after it', function () {
        $end = CarbonImmutable::parse('2025-12-31');
        $range = DateRange::until($end);

        $now = CarbonImmutable::parse('2026-01-01');

        expect($range->isActive($now))->toBeFalse();
    });

    test('startsAt is alias for startDate', function () {
        $start = CarbonImmutable::parse('2025-01-01');
        $range = DateRange::from($start);

        expect($range->startsAt())->toEqual($start)
            ->and($range->startsAt())->toEqual($range->startDate());
    });

    test('endsAt is alias for endDate', function () {
        $end = CarbonImmutable::parse('2025-12-31');
        $range = DateRange::until($end);

        expect($range->endsAt())->toEqual($end)
            ->and($range->endsAt())->toEqual($range->endDate());
    });

    test('two ranges with same dates are equal', function () {
        $start = CarbonImmutable::parse('2025-01-01');
        $end = CarbonImmutable::parse('2025-12-31');
        $range1 = DateRange::create($start, $end);
        $range2 = DateRange::create($start, $end);

        expect($range1->equals($range2))->toBeTrue();
    });

    test('two unlimited ranges are equal', function () {
        $range1 = DateRange::unlimited();
        $range2 = DateRange::unlimited();

        expect($range1->equals($range2))->toBeTrue();
    });

    test('two ranges with different dates are not equal', function () {
        $range1 = DateRange::create(
            CarbonImmutable::parse('2025-01-01'),
            CarbonImmutable::parse('2025-12-31')
        );
        $range2 = DateRange::create(
            CarbonImmutable::parse('2025-02-01'),
            CarbonImmutable::parse('2025-12-31')
        );

        expect($range1->equals($range2))->toBeFalse();
    });

    test('equals returns false for different value object types', function () {
        $range = DateRange::unlimited();
        $text = QuestionText::fromString('Text');

        expect($range->equals($text))->toBeFalse();
    });

    test('value returns array with ISO8601 formatted dates', function () {
        $start = CarbonImmutable::parse('2025-01-01');
        $end = CarbonImmutable::parse('2025-12-31');
        $range = DateRange::create($start, $end);

        $value = $range->value();

        expect($value)->toBeArray()
            ->and($value)->toHaveKey('start_date')
            ->and($value)->toHaveKey('end_date')
            ->and($value['start_date'])->toBeString()
            ->and($value['end_date'])->toBeString();
    });

    test('value returns nulls for unlimited range', function () {
        $range = DateRange::unlimited();

        $value = $range->value();

        expect($value['start_date'])->toBeNull()
            ->and($value['end_date'])->toBeNull();
    });

    test('string representation returns json for unlimited range', function () {
        $range = DateRange::unlimited();
        $str = (string) $range;

        expect($str)->toBeJson();
    });

    test('json serializes to value array', function () {
        $start = CarbonImmutable::parse('2025-01-01');
        $end = CarbonImmutable::parse('2025-12-31');
        $range = DateRange::create($start, $end);

        expect($range->jsonSerialize())->toBe($range->value());
    });
});

describe('QuestionnaireSettings Value Object', function () {
    test('can be created with all parameters', function () {
        $settings = QuestionnaireSettings::create(
            allowAnonymous: true,
            allowMultipleSubmissions: false,
            duplicateSubmissionStrategy: DuplicateSubmissionStrategy::PREVENT_BY_USER,
            sendNotifications: true,
            notificationEmail: 'test@example.com',
            maxSubmissions: 100
        );

        expect($settings->allowAnonymous())->toBeTrue()
            ->and($settings->allowMultipleSubmissions())->toBeFalse()
            ->and($settings->duplicateSubmissionStrategy())->toBe(DuplicateSubmissionStrategy::PREVENT_BY_USER)
            ->and($settings->sendNotifications())->toBeTrue()
            ->and($settings->notificationEmail())->toBe('test@example.com')
            ->and($settings->maxSubmissions())->toBe(100);
    });

    test('can be created with default values', function () {
        $settings = QuestionnaireSettings::default();

        expect($settings->allowAnonymous())->toBeTrue()
            ->and($settings->allowMultipleSubmissions())->toBeFalse()
            ->and($settings->duplicateSubmissionStrategy())->toBe(DuplicateSubmissionStrategy::ALLOW_MULTIPLE)
            ->and($settings->sendNotifications())->toBeFalse()
            ->and($settings->notificationEmail())->toBeNull()
            ->and($settings->maxSubmissions())->toBe(0);
    });

    test('create uses default strategy when not provided', function () {
        $settings = QuestionnaireSettings::create();

        expect($settings->duplicateSubmissionStrategy())->toBe(DuplicateSubmissionStrategy::ALLOW_MULTIPLE);
    });

    test('hasMaxSubmissions returns true when max is greater than zero', function () {
        $settings = QuestionnaireSettings::create(maxSubmissions: 10);

        expect($settings->hasMaxSubmissions())->toBeTrue();
    });

    test('hasMaxSubmissions returns false when max is zero', function () {
        $settings = QuestionnaireSettings::create(maxSubmissions: 0);

        expect($settings->hasMaxSubmissions())->toBeFalse();
    });

    test('two settings with same values are equal', function () {
        $settings1 = QuestionnaireSettings::create(
            allowAnonymous: true,
            allowMultipleSubmissions: false,
            duplicateSubmissionStrategy: DuplicateSubmissionStrategy::PREVENT_BY_USER,
            sendNotifications: false,
            notificationEmail: null,
            maxSubmissions: 0
        );
        $settings2 = QuestionnaireSettings::create(
            allowAnonymous: true,
            allowMultipleSubmissions: false,
            duplicateSubmissionStrategy: DuplicateSubmissionStrategy::PREVENT_BY_USER,
            sendNotifications: false,
            notificationEmail: null,
            maxSubmissions: 0
        );

        expect($settings1->equals($settings2))->toBeTrue();
    });

    test('two settings with different values are not equal', function () {
        $settings1 = QuestionnaireSettings::create(allowAnonymous: true);
        $settings2 = QuestionnaireSettings::create(allowAnonymous: false);

        expect($settings1->equals($settings2))->toBeFalse();
    });

    test('equals returns false for different value object types', function () {
        $settings = QuestionnaireSettings::default();
        $text = QuestionText::fromString('Text');

        expect($settings->equals($text))->toBeFalse();
    });

    test('value returns array representation', function () {
        $settings = QuestionnaireSettings::create(
            allowAnonymous: true,
            allowMultipleSubmissions: false,
            duplicateSubmissionStrategy: DuplicateSubmissionStrategy::PREVENT_BY_USER,
            sendNotifications: true,
            notificationEmail: 'test@example.com',
            maxSubmissions: 100
        );

        $value = $settings->value();

        expect($value)->toBeArray()
            ->and($value)->toHaveKey('allow_anonymous')
            ->and($value)->toHaveKey('allow_multiple_submissions')
            ->and($value)->toHaveKey('duplicate_submission_strategy')
            ->and($value)->toHaveKey('send_notifications')
            ->and($value)->toHaveKey('notification_email')
            ->and($value)->toHaveKey('max_submissions');
    });

    test('toArray returns same as value', function () {
        $settings = QuestionnaireSettings::default();

        expect($settings->toArray())->toBe($settings->value());
    });

    test('value contains correct values', function () {
        $settings = QuestionnaireSettings::create(
            allowAnonymous: false,
            allowMultipleSubmissions: true,
            duplicateSubmissionStrategy: DuplicateSubmissionStrategy::PREVENT_BY_IP,
            sendNotifications: true,
            notificationEmail: 'admin@example.com',
            maxSubmissions: 50
        );

        $value = $settings->value();

        expect($value['allow_anonymous'])->toBeFalse()
            ->and($value['allow_multiple_submissions'])->toBeTrue()
            ->and($value['duplicate_submission_strategy'])->toBe(DuplicateSubmissionStrategy::PREVENT_BY_IP->value)
            ->and($value['send_notifications'])->toBeTrue()
            ->and($value['notification_email'])->toBe('admin@example.com')
            ->and($value['max_submissions'])->toBe(50);
    });

    test('string representation returns json', function () {
        $settings = QuestionnaireSettings::default();
        $str = (string) $settings;

        expect($str)->toBeJson();
    });

    test('json serializes to value array', function () {
        $settings = QuestionnaireSettings::default();

        expect($settings->jsonSerialize())->toBe($settings->value());
    });
});
