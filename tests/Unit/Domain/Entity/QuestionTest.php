<?php

declare(strict_types=1);

use Liangjin0228\Questionnaire\Domain\Questionnaire\Entity\Question;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionOptions;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionText;

describe('Question Entity - Creation', function () {
    test('creates question with static factory', function () {
        $questionId = QuestionId::fromString(testUuid());
        $questionText = QuestionText::fromString('What is your name?');
        $options = QuestionOptions::fromArray(['Option 1', 'Option 2']);

        $question = Question::create(
            $questionId,
            $questionText,
            'radio',
            $options,
            true,
            1,
            'Please provide your full name',
            ['max_length' => 100]
        );

        expect($question)->toBeInstanceOf(Question::class);
    });

    test('creates question with minimal parameters', function () {
        $questionId = QuestionId::fromString(testUuid());
        $questionText = QuestionText::fromString('Simple question');
        $options = QuestionOptions::empty();

        $question = Question::create(
            $questionId,
            $questionText,
            'text',
            $options,
            false,
            1
        );

        expect($question)->toBeInstanceOf(Question::class)
            ->and($question->description())->toBeNull()
            ->and($question->settings())->toBe([]);
    });

    test('creates question with null description', function () {
        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Question'),
            'text',
            QuestionOptions::empty(),
            true,
            1,
            null,
            []
        );

        expect($question->description())->toBeNull();
    });

    test('creates question with empty settings array', function () {
        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Question'),
            'text',
            QuestionOptions::empty(),
            true,
            1,
            'Description',
            []
        );

        expect($question->settings())->toBe([]);
    });
});

describe('Question Entity - Property Getters', function () {
    test('id returns QuestionId', function () {
        $questionId = QuestionId::fromString(testUuid());

        $question = Question::create(
            $questionId,
            QuestionText::fromString('Question'),
            'text',
            QuestionOptions::empty(),
            true,
            1
        );

        expect($question->id())->toBe($questionId)
            ->and($question->id())->toBeInstanceOf(QuestionId::class);
    });

    test('text returns QuestionText', function () {
        $questionText = QuestionText::fromString('What is your favorite color?');

        $question = Question::create(
            QuestionId::fromString(testUuid()),
            $questionText,
            'text',
            QuestionOptions::empty(),
            true,
            1
        );

        expect($question->text())->toBe($questionText)
            ->and($question->text())->toBeInstanceOf(QuestionText::class);
    });

    test('type returns string', function () {
        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Question'),
            'radio',
            QuestionOptions::empty(),
            true,
            1
        );

        expect($question->type())->toBe('radio')
            ->and($question->type())->toBeString();
    });

    test('options returns QuestionOptions', function () {
        $options = QuestionOptions::fromArray(['Yes', 'No', 'Maybe']);

        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Question'),
            'radio',
            $options,
            true,
            1
        );

        expect($question->options())->toBe($options)
            ->and($question->options())->toBeInstanceOf(QuestionOptions::class);
    });

    test('isRequired returns boolean', function () {
        $requiredQuestion = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Required'),
            'text',
            QuestionOptions::empty(),
            true,
            1
        );

        $optionalQuestion = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Optional'),
            'text',
            QuestionOptions::empty(),
            false,
            2
        );

        expect($requiredQuestion->isRequired())->toBeTrue()
            ->and($optionalQuestion->isRequired())->toBeFalse();
    });

    test('order returns integer', function () {
        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Question'),
            'text',
            QuestionOptions::empty(),
            true,
            42
        );

        expect($question->order())->toBe(42)
            ->and($question->order())->toBeInt();
    });

    test('description returns nullable string', function () {
        $questionWithDescription = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Question'),
            'text',
            QuestionOptions::empty(),
            true,
            1,
            'This is a helpful description'
        );

        $questionWithoutDescription = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Question'),
            'text',
            QuestionOptions::empty(),
            true,
            2
        );

        expect($questionWithDescription->description())->toBe('This is a helpful description')
            ->and($questionWithoutDescription->description())->toBeNull();
    });

    test('settings returns array', function () {
        $settings = ['min' => 1, 'max' => 100, 'step' => 5];

        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Question'),
            'number',
            QuestionOptions::empty(),
            true,
            1,
            null,
            $settings
        );

        expect($question->settings())->toBe($settings)
            ->and($question->settings())->toBeArray();
    });
});

describe('Question Entity - Mutation Methods', function () {
    test('updateText changes question text', function () {
        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Original text'),
            'text',
            QuestionOptions::empty(),
            true,
            1
        );

        $newText = QuestionText::fromString('Updated text');
        $question->updateText($newText);

        expect($question->text())->toBe($newText)
            ->and($question->text()->value())->toBe('Updated text');
    });

    test('updateOptions changes question options', function () {
        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Question'),
            'radio',
            QuestionOptions::fromArray(['Old 1', 'Old 2']),
            true,
            1
        );

        $newOptions = QuestionOptions::fromArray(['New 1', 'New 2', 'New 3']);
        $question->updateOptions($newOptions);

        expect($question->options())->toBe($newOptions)
            ->and($question->options()->count())->toBe(3);
    });

    test('markAsRequired sets required to true', function () {
        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Question'),
            'text',
            QuestionOptions::empty(),
            false,
            1
        );

        expect($question->isRequired())->toBeFalse();

        $question->markAsRequired();

        expect($question->isRequired())->toBeTrue();
    });

    test('markAsOptional sets required to false', function () {
        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Question'),
            'text',
            QuestionOptions::empty(),
            true,
            1
        );

        expect($question->isRequired())->toBeTrue();

        $question->markAsOptional();

        expect($question->isRequired())->toBeFalse();
    });

    test('updateOrder changes question order', function () {
        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Question'),
            'text',
            QuestionOptions::empty(),
            true,
            1
        );

        expect($question->order())->toBe(1);

        $question->updateOrder(5);

        expect($question->order())->toBe(5);
    });

    test('updateDescription changes description', function () {
        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Question'),
            'text',
            QuestionOptions::empty(),
            true,
            1,
            'Original description'
        );

        expect($question->description())->toBe('Original description');

        $question->updateDescription('New description');

        expect($question->description())->toBe('New description');
    });

    test('updateDescription can set to null', function () {
        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Question'),
            'text',
            QuestionOptions::empty(),
            true,
            1,
            'Has description'
        );

        expect($question->description())->toBe('Has description');

        $question->updateDescription(null);

        expect($question->description())->toBeNull();
    });

    test('updateSettings changes settings', function () {
        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Question'),
            'number',
            QuestionOptions::empty(),
            true,
            1,
            null,
            ['min' => 0, 'max' => 10]
        );

        expect($question->settings())->toBe(['min' => 0, 'max' => 10]);

        $newSettings = ['min' => 1, 'max' => 100, 'step' => 5];
        $question->updateSettings($newSettings);

        expect($question->settings())->toBe($newSettings)
            ->and($question->settings())->toHaveKey('step')
            ->and($question->settings()['step'])->toBe(5);
    });

    test('updateSettings can set to empty array', function () {
        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Question'),
            'text',
            QuestionOptions::empty(),
            true,
            1,
            null,
            ['some' => 'setting']
        );

        expect($question->settings())->not->toBeEmpty();

        $question->updateSettings([]);

        expect($question->settings())->toBe([])
            ->and($question->settings())->toBeEmpty();
    });
});

describe('Question Entity - Identity and Equality', function () {
    test('two questions with same id are equal', function () {
        $questionId = QuestionId::fromString(testUuid());

        $question1 = Question::create(
            $questionId,
            QuestionText::fromString('First question'),
            'text',
            QuestionOptions::empty(),
            true,
            1
        );

        $question2 = Question::create(
            $questionId,
            QuestionText::fromString('Second question'),
            'radio',
            QuestionOptions::fromArray(['A', 'B']),
            false,
            2
        );

        expect($question1->equals($question2))->toBeTrue()
            ->and($question2->equals($question1))->toBeTrue();
    });

    test('two questions with different ids are not equal', function () {
        $question1 = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Question'),
            'text',
            QuestionOptions::empty(),
            true,
            1
        );

        $question2 = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Question'),
            'text',
            QuestionOptions::empty(),
            true,
            1
        );

        expect($question1->equals($question2))->toBeFalse()
            ->and($question2->equals($question1))->toBeFalse();
    });

    test('id method returns consistent value', function () {
        $questionId = QuestionId::fromString(testUuid());

        $question = Question::create(
            $questionId,
            QuestionText::fromString('Question'),
            'text',
            QuestionOptions::empty(),
            true,
            1
        );

        expect($question->id())->toBe($questionId)
            ->and($question->id())->toBe($question->id());
    });
});

describe('Question Entity - Value Object Integration', function () {
    test('works with various question types', function () {
        $types = ['text', 'textarea', 'radio', 'checkbox', 'select', 'date', 'number', 'email'];

        foreach ($types as $type) {
            $question = Question::create(
                QuestionId::fromString(testUuid()),
                QuestionText::fromString('Question for '.$type),
                $type,
                QuestionOptions::empty(),
                true,
                1
            );

            expect($question->type())->toBe($type);
        }
    });

    test('preserves value object immutability', function () {
        $originalText = QuestionText::fromString('Original');
        $question = Question::create(
            QuestionId::fromString(testUuid()),
            $originalText,
            'text',
            QuestionOptions::empty(),
            true,
            1
        );

        $newText = QuestionText::fromString('Updated');
        $question->updateText($newText);

        expect($originalText->value())->toBe('Original')
            ->and($question->text()->value())->toBe('Updated');
    });

    test('options can be empty for non-choice questions', function () {
        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Free text question'),
            'textarea',
            QuestionOptions::empty(),
            true,
            1
        );

        expect($question->options()->isEmpty())->toBeTrue()
            ->and($question->options()->count())->toBe(0);
    });

    test('options can contain multiple values for choice questions', function () {
        $options = QuestionOptions::fromArray([
            'Strongly Disagree',
            'Disagree',
            'Neutral',
            'Agree',
            'Strongly Agree',
        ]);

        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Rate your satisfaction'),
            'radio',
            $options,
            true,
            1
        );

        expect($question->options()->count())->toBe(5)
            ->and($question->options()->hasOption('Neutral'))->toBeTrue();
    });
});

describe('Question Entity - Complex Scenarios', function () {
    test('handles multiple updates correctly', function () {
        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Original'),
            'text',
            QuestionOptions::empty(),
            true,
            1,
            'Original description',
            ['original' => 'setting']
        );

        $question->updateText(QuestionText::fromString('Updated text'));
        $question->markAsOptional();
        $question->updateOrder(3);
        $question->updateDescription('Updated description');
        $question->updateSettings(['updated' => 'setting']);

        expect($question->text()->value())->toBe('Updated text')
            ->and($question->isRequired())->toBeFalse()
            ->and($question->order())->toBe(3)
            ->and($question->description())->toBe('Updated description')
            ->and($question->settings())->toBe(['updated' => 'setting']);
    });

    test('required flag can be toggled multiple times', function () {
        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Question'),
            'text',
            QuestionOptions::empty(),
            false,
            1
        );

        expect($question->isRequired())->toBeFalse();

        $question->markAsRequired();
        expect($question->isRequired())->toBeTrue();

        $question->markAsOptional();
        expect($question->isRequired())->toBeFalse();

        $question->markAsRequired();
        expect($question->isRequired())->toBeTrue();
    });

    test('order can be updated to maintain question sequence', function () {
        $question1 = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('First'),
            'text',
            QuestionOptions::empty(),
            true,
            1
        );

        $question2 = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Second'),
            'text',
            QuestionOptions::empty(),
            true,
            2
        );

        $question1->updateOrder(2);
        $question2->updateOrder(1);

        expect($question1->order())->toBe(2)
            ->and($question2->order())->toBe(1);
    });

    test('settings can store complex configuration', function () {
        $complexSettings = [
            'validation' => [
                'min' => 0,
                'max' => 100,
                'pattern' => '^[0-9]+$',
            ],
            'ui' => [
                'placeholder' => 'Enter a number',
                'hint' => 'Must be between 0 and 100',
            ],
            'conditional' => [
                'show_if' => 'previous_answer_is_yes',
            ],
        ];

        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Complex question'),
            'number',
            QuestionOptions::empty(),
            true,
            1,
            null,
            $complexSettings
        );

        expect($question->settings())->toBe($complexSettings)
            ->and($question->settings()['validation']['max'])->toBe(100)
            ->and($question->settings()['ui']['placeholder'])->toBe('Enter a number');
    });
});
