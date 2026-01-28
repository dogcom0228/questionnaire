<?php

declare(strict_types=1);

use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionId;
use Liangjin0228\Questionnaire\Domain\Response\Entity\Answer;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\AnswerId;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\AnswerValue;

describe('Answer Entity - Creation', function () {
    test('creates answer with static factory', function () {
        $answerId = AnswerId::fromString(testUuid());
        $questionId = QuestionId::fromString(testUuid());
        $answerValue = AnswerValue::fromString('My answer');

        $answer = Answer::create($answerId, $questionId, $answerValue);

        expect($answer)->toBeInstanceOf(Answer::class);
    });

    test('creates answer with string value', function () {
        $answer = Answer::create(
            AnswerId::fromString(testUuid()),
            QuestionId::fromString(testUuid()),
            AnswerValue::fromString('Text answer')
        );

        expect($answer->value()->isString())->toBeTrue()
            ->and($answer->value()->toMixed())->toBe('Text answer');
    });

    test('creates answer with array value', function () {
        $answer = Answer::create(
            AnswerId::fromString(testUuid()),
            QuestionId::fromString(testUuid()),
            AnswerValue::fromArray(['Option 1', 'Option 2'])
        );

        expect($answer->value()->isArray())->toBeTrue()
            ->and($answer->value()->toMixed())->toBe(['Option 1', 'Option 2']);
    });

    test('creates answer with numeric value', function () {
        $answer = Answer::create(
            AnswerId::fromString(testUuid()),
            QuestionId::fromString(testUuid()),
            AnswerValue::fromInt(42)
        );

        expect($answer->value()->isNumeric())->toBeTrue()
            ->and($answer->value()->toMixed())->toBe(42);
    });

    test('creates answer with boolean value', function () {
        $answer = Answer::create(
            AnswerId::fromString(testUuid()),
            QuestionId::fromString(testUuid()),
            AnswerValue::fromBool(true)
        );

        expect($answer->value()->isBool())->toBeTrue()
            ->and($answer->value()->toMixed())->toBe(true);
    });
});

describe('Answer Entity - Property Getters', function () {
    test('id returns AnswerId inherited from Entity', function () {
        $answerId = AnswerId::fromString(testUuid());

        $answer = Answer::create(
            $answerId,
            QuestionId::fromString(testUuid()),
            AnswerValue::fromString('Value')
        );

        expect($answer->id())->toBe($answerId)
            ->and($answer->id())->toBeInstanceOf(AnswerId::class);
    });

    test('questionId returns QuestionId', function () {
        $questionId = QuestionId::fromString(testUuid());

        $answer = Answer::create(
            AnswerId::fromString(testUuid()),
            $questionId,
            AnswerValue::fromString('Value')
        );

        expect($answer->questionId())->toBe($questionId)
            ->and($answer->questionId())->toBeInstanceOf(QuestionId::class);
    });

    test('value returns AnswerValue', function () {
        $answerValue = AnswerValue::fromString('My answer');

        $answer = Answer::create(
            AnswerId::fromString(testUuid()),
            QuestionId::fromString(testUuid()),
            $answerValue
        );

        expect($answer->value())->toBe($answerValue)
            ->and($answer->value())->toBeInstanceOf(AnswerValue::class);
    });

    test('questionId is immutable', function () {
        $questionId = QuestionId::fromString(testUuid());

        $answer = Answer::create(
            AnswerId::fromString(testUuid()),
            $questionId,
            AnswerValue::fromString('Value')
        );

        expect($answer->questionId())->toBe($questionId)
            ->and($answer->questionId())->toBe($answer->questionId());
    });
});

describe('Answer Entity - Mutation Methods', function () {
    test('updateValue changes answer value', function () {
        $answer = Answer::create(
            AnswerId::fromString(testUuid()),
            QuestionId::fromString(testUuid()),
            AnswerValue::fromString('Original value')
        );

        expect($answer->value()->toMixed())->toBe('Original value');

        $newValue = AnswerValue::fromString('Updated value');
        $answer->updateValue($newValue);

        expect($answer->value())->toBe($newValue)
            ->and($answer->value()->toMixed())->toBe('Updated value');
    });

    test('updateValue can change value type from string to array', function () {
        $answer = Answer::create(
            AnswerId::fromString(testUuid()),
            QuestionId::fromString(testUuid()),
            AnswerValue::fromString('Single answer')
        );

        expect($answer->value()->isString())->toBeTrue();

        $newValue = AnswerValue::fromArray(['Multiple', 'Answers']);
        $answer->updateValue($newValue);

        expect($answer->value()->isArray())->toBeTrue()
            ->and($answer->value()->toMixed())->toBe(['Multiple', 'Answers']);
    });

    test('updateValue can change value type from array to string', function () {
        $answer = Answer::create(
            AnswerId::fromString(testUuid()),
            QuestionId::fromString(testUuid()),
            AnswerValue::fromArray(['A', 'B'])
        );

        expect($answer->value()->isArray())->toBeTrue();

        $newValue = AnswerValue::fromString('Single');
        $answer->updateValue($newValue);

        expect($answer->value()->isString())->toBeTrue()
            ->and($answer->value()->toMixed())->toBe('Single');
    });

    test('updateValue preserves original value object immutability', function () {
        $originalValue = AnswerValue::fromString('Original');

        $answer = Answer::create(
            AnswerId::fromString(testUuid()),
            QuestionId::fromString(testUuid()),
            $originalValue
        );

        $newValue = AnswerValue::fromString('Updated');
        $answer->updateValue($newValue);

        expect($originalValue->toMixed())->toBe('Original')
            ->and($answer->value()->toMixed())->toBe('Updated');
    });
});

describe('Answer Entity - Identity and Equality', function () {
    test('two answers with same id are equal', function () {
        $answerId = AnswerId::fromString(testUuid());

        $answer1 = Answer::create(
            $answerId,
            QuestionId::fromString(testUuid()),
            AnswerValue::fromString('First answer')
        );

        $answer2 = Answer::create(
            $answerId,
            QuestionId::fromString(testUuid()),
            AnswerValue::fromString('Second answer')
        );

        expect($answer1->equals($answer2))->toBeTrue()
            ->and($answer2->equals($answer1))->toBeTrue();
    });

    test('two answers with different ids are not equal', function () {
        $questionId = QuestionId::fromString(testUuid());
        $answerValue = AnswerValue::fromString('Same value');

        $answer1 = Answer::create(
            AnswerId::fromString(testUuid()),
            $questionId,
            $answerValue
        );

        $answer2 = Answer::create(
            AnswerId::fromString(testUuid()),
            $questionId,
            $answerValue
        );

        expect($answer1->equals($answer2))->toBeFalse()
            ->and($answer2->equals($answer1))->toBeFalse();
    });

    test('id method returns consistent value', function () {
        $answerId = AnswerId::fromString(testUuid());

        $answer = Answer::create(
            $answerId,
            QuestionId::fromString(testUuid()),
            AnswerValue::fromString('Value')
        );

        expect($answer->id())->toBe($answerId)
            ->and($answer->id())->toBe($answer->id());
    });

    test('equality is based on id not on value or questionId', function () {
        $answerId = AnswerId::fromString(testUuid());

        $answer1 = Answer::create(
            $answerId,
            QuestionId::fromString(testUuid()),
            AnswerValue::fromString('Value 1')
        );

        $answer2 = Answer::create(
            $answerId,
            QuestionId::fromString(testUuid()),
            AnswerValue::fromString('Value 2')
        );

        expect($answer1->equals($answer2))->toBeTrue();
    });
});

describe('Answer Entity - Value Object Integration', function () {
    test('works with text answer type', function () {
        $answer = Answer::create(
            AnswerId::fromString(testUuid()),
            QuestionId::fromString(testUuid()),
            AnswerValue::fromString('This is a long text answer to an essay question.')
        );

        expect($answer->value()->isString())->toBeTrue()
            ->and($answer->value()->toMixed())->toBeString();
    });

    test('works with multiple choice answer type', function () {
        $answer = Answer::create(
            AnswerId::fromString(testUuid()),
            QuestionId::fromString(testUuid()),
            AnswerValue::fromArray(['Option A', 'Option C', 'Option E'])
        );

        expect($answer->value()->isArray())->toBeTrue()
            ->and($answer->value()->toMixed())->toHaveCount(3);
    });

    test('works with numeric answer type', function () {
        $answer = Answer::create(
            AnswerId::fromString(testUuid()),
            QuestionId::fromString(testUuid()),
            AnswerValue::fromInt(100)
        );

        expect($answer->value()->isNumeric())->toBeTrue()
            ->and($answer->value()->toMixed())->toBe(100);
    });

    test('works with boolean answer type', function () {
        $answer = Answer::create(
            AnswerId::fromString(testUuid()),
            QuestionId::fromString(testUuid()),
            AnswerValue::fromBool(false)
        );

        expect($answer->value()->isBool())->toBeTrue()
            ->and($answer->value()->toMixed())->toBe(false);
    });

    test('maintains reference to question through QuestionId', function () {
        $questionId = QuestionId::fromString(testUuid());

        $answer = Answer::create(
            AnswerId::fromString(testUuid()),
            $questionId,
            AnswerValue::fromString('Answer')
        );

        expect($answer->questionId())->toBe($questionId)
            ->and($answer->questionId()->toString())->toBe($questionId->toString());
    });
});

describe('Answer Entity - Complex Scenarios', function () {
    test('handles multiple value updates correctly', function () {
        $answer = Answer::create(
            AnswerId::fromString(testUuid()),
            QuestionId::fromString(testUuid()),
            AnswerValue::fromString('First')
        );

        expect($answer->value()->toMixed())->toBe('First');

        $answer->updateValue(AnswerValue::fromString('Second'));
        expect($answer->value()->toMixed())->toBe('Second');

        $answer->updateValue(AnswerValue::fromString('Third'));
        expect($answer->value()->toMixed())->toBe('Third');

        $answer->updateValue(AnswerValue::fromString('Final'));
        expect($answer->value()->toMixed())->toBe('Final');
    });

    test('answer for checkbox question can be updated from single to multiple', function () {
        $answer = Answer::create(
            AnswerId::fromString(testUuid()),
            QuestionId::fromString(testUuid()),
            AnswerValue::fromArray(['Option A'])
        );

        expect($answer->value()->toMixed())->toHaveCount(1);

        $answer->updateValue(AnswerValue::fromArray(['Option A', 'Option B', 'Option C']));

        expect($answer->value()->toMixed())->toHaveCount(3)
            ->and($answer->value()->toMixed())->toContain('Option B');
    });

    test('answer for numeric question can be updated', function () {
        $answer = Answer::create(
            AnswerId::fromString(testUuid()),
            QuestionId::fromString(testUuid()),
            AnswerValue::fromInt(5)
        );

        expect($answer->value()->toMixed())->toBe(5);

        $answer->updateValue(AnswerValue::fromInt(10));

        expect($answer->value()->toMixed())->toBe(10);
    });

    test('answer maintains identity across value updates', function () {
        $answerId = AnswerId::fromString(testUuid());

        $answer = Answer::create(
            $answerId,
            QuestionId::fromString(testUuid()),
            AnswerValue::fromString('Original')
        );

        $clonedAnswer = Answer::create(
            $answerId,
            QuestionId::fromString(testUuid()),
            AnswerValue::fromString('Different')
        );

        expect($answer->equals($clonedAnswer))->toBeTrue();

        $answer->updateValue(AnswerValue::fromString('Updated'));

        expect($answer->equals($clonedAnswer))->toBeTrue()
            ->and($answer->id())->toBe($clonedAnswer->id());
    });

    test('multiple answers for same question have different ids', function () {
        $questionId = QuestionId::fromString(testUuid());

        $answer1 = Answer::create(
            AnswerId::fromString(testUuid()),
            $questionId,
            AnswerValue::fromString('First response')
        );

        $answer2 = Answer::create(
            AnswerId::fromString(testUuid()),
            $questionId,
            AnswerValue::fromString('Second response')
        );

        expect($answer1->questionId())->toBe($questionId)
            ->and($answer2->questionId())->toBe($questionId)
            ->and($answer1->equals($answer2))->toBeFalse();
    });

    test('answer can store complex nested array value', function () {
        $complexValue = [
            'selected_options' => ['A', 'B', 'C'],
            'other_text' => 'Custom input',
            'metadata' => [
                'timestamp' => '2024-01-01 12:00:00',
                'source' => 'mobile',
            ],
        ];

        $answer = Answer::create(
            AnswerId::fromString(testUuid()),
            QuestionId::fromString(testUuid()),
            AnswerValue::fromMixed($complexValue)
        );

        expect($answer->value()->isArray())->toBeTrue()
            ->and($answer->value()->toMixed())->toBe($complexValue)
            ->and($answer->value()->toMixed()['selected_options'])->toHaveCount(3)
            ->and($answer->value()->toMixed()['other_text'])->toBe('Custom input');
    });
});
