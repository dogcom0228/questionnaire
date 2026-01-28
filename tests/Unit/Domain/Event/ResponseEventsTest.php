<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Response\Entity\Answer;
use Liangjin0228\Questionnaire\Domain\Response\Event\ResponseSubmitted;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\AnswerId;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\AnswerValue;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\IpAddress;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\Respondent;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\ResponseId;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\UserAgent;

describe('ResponseSubmitted Event', function () {
    test('can be instantiated with all required properties', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::authenticated('user', 'user-123');
        $ipAddress = IpAddress::fromString('192.168.1.100');
        $userAgent = UserAgent::fromString('Mozilla/5.0');

        $question1Id = QuestionId::fromString(testUuid());
        $answer1 = Answer::create(
            AnswerId::generate(),
            $question1Id,
            AnswerValue::fromMixed('Answer text')
        );

        $answers = [$question1Id->toString() => $answer1];
        $metadata = ['source' => 'web', 'device' => 'desktop'];
        $submittedAt = CarbonImmutable::now();

        $event = new ResponseSubmitted(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            $answers,
            $metadata,
            $submittedAt
        );

        expect($event->responseId)->toBe($responseId)
            ->and($event->questionnaireId)->toBe($questionnaireId)
            ->and($event->respondent)->toBe($respondent)
            ->and($event->ipAddress)->toBe($ipAddress)
            ->and($event->userAgent)->toBe($userAgent)
            ->and($event->answers)->toBe($answers)
            ->and($event->metadata)->toBe($metadata)
            ->and($event->submittedAt)->toBe($submittedAt);
    });

    test('sets aggregate root uuid from response id', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::anonymous();
        $ipAddress = IpAddress::fromString('10.0.0.1');
        $userAgent = UserAgent::fromString('TestAgent');

        $questionId = QuestionId::fromString(testUuid());
        $answer = Answer::create(
            AnswerId::generate(),
            $questionId,
            AnswerValue::fromMixed('Answer')
        );

        $event = new ResponseSubmitted(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            [$questionId->toString() => $answer],
            [],
            CarbonImmutable::now()
        );

        expect($event->aggregateRootUuid())->toBe($responseId->toString());
    });

    test('toArray returns correct structure with anonymous respondent', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::anonymous();
        $ipAddress = IpAddress::fromString('203.0.113.45');
        $userAgent = UserAgent::fromString('Chrome/96.0');

        $question1Id = QuestionId::fromString(testUuid());
        $question2Id = QuestionId::fromString(testUuid());

        $answer1Id = AnswerId::generate();
        $answer2Id = AnswerId::generate();

        $answer1 = Answer::create($answer1Id, $question1Id, AnswerValue::fromMixed('Text answer'));
        $answer2 = Answer::create($answer2Id, $question2Id, AnswerValue::fromMixed(42));

        $answers = [
            $question1Id->toString() => $answer1,
            $question2Id->toString() => $answer2,
        ];

        $metadata = ['browser' => 'chrome', 'os' => 'windows'];
        $submittedAt = CarbonImmutable::parse('2024-06-15 14:30:00');

        $event = new ResponseSubmitted(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            $answers,
            $metadata,
            $submittedAt
        );

        $array = $event->toArray();

        expect($array)->toBeArray()
            ->and($array)->toHaveKey('response_id')
            ->and($array)->toHaveKey('questionnaire_id')
            ->and($array)->toHaveKey('respondent')
            ->and($array)->toHaveKey('ip_address')
            ->and($array)->toHaveKey('user_agent')
            ->and($array)->toHaveKey('answers')
            ->and($array)->toHaveKey('metadata')
            ->and($array)->toHaveKey('submitted_at')
            ->and($array['response_id'])->toBe($responseId->toString())
            ->and($array['questionnaire_id'])->toBe($questionnaireId->toString())
            ->and($array['respondent']['type'])->toBeNull()
            ->and($array['respondent']['id'])->toBeNull()
            ->and($array['ip_address'])->toBe('203.0.113.45')
            ->and($array['user_agent'])->toBe('Chrome/96.0')
            ->and($array['metadata'])->toBe($metadata)
            ->and($array['submitted_at'])->toBe($submittedAt->toIso8601String());
    });

    test('toArray returns correct structure with authenticated respondent', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::authenticated('admin', 'admin-456');
        $ipAddress = IpAddress::fromString('192.168.1.1');
        $userAgent = UserAgent::fromString('Firefox/91.0');

        $questionId = QuestionId::fromString(testUuid());
        $answerId = AnswerId::generate();
        $answer = Answer::create($answerId, $questionId, AnswerValue::fromMixed(['option1', 'option2']));

        $answers = [$questionId->toString() => $answer];
        $metadata = [];
        $submittedAt = CarbonImmutable::now();

        $event = new ResponseSubmitted(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            $answers,
            $metadata,
            $submittedAt
        );

        $array = $event->toArray();

        expect($array['respondent']['type'])->toBe('admin')
            ->and($array['respondent']['id'])->toBe('admin-456');
    });

    test('toArray correctly serializes answers array', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::anonymous();
        $ipAddress = IpAddress::fromString('10.20.30.40');
        $userAgent = UserAgent::fromString('Safari/14.0');

        $question1Id = QuestionId::fromString(testUuid());
        $question2Id = QuestionId::fromString(testUuid());
        $question3Id = QuestionId::fromString(testUuid());

        $answer1Id = AnswerId::generate();
        $answer2Id = AnswerId::generate();
        $answer3Id = AnswerId::generate();

        $answer1 = Answer::create($answer1Id, $question1Id, AnswerValue::fromMixed('String answer'));
        $answer2 = Answer::create($answer2Id, $question2Id, AnswerValue::fromMixed(123));
        $answer3 = Answer::create($answer3Id, $question3Id, AnswerValue::fromMixed(true));

        $answers = [
            $question1Id->toString() => $answer1,
            $question2Id->toString() => $answer2,
            $question3Id->toString() => $answer3,
        ];

        $event = new ResponseSubmitted(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            $answers,
            [],
            CarbonImmutable::now()
        );

        $array = $event->toArray();

        expect($array['answers'])->toBeArray()
            ->and($array['answers'])->toHaveKey($question1Id->toString())
            ->and($array['answers'])->toHaveKey($question2Id->toString())
            ->and($array['answers'])->toHaveKey($question3Id->toString())
            ->and($array['answers'][$question1Id->toString()])->toBeArray()
            ->and($array['answers'][$question1Id->toString()]['answer_id'])->toBe($answer1Id->toString())
            ->and($array['answers'][$question1Id->toString()]['question_id'])->toBe($question1Id->toString())
            ->and($array['answers'][$question1Id->toString()]['value'])->toBe('String answer')
            ->and($array['answers'][$question2Id->toString()]['value'])->toBe(123)
            ->and($array['answers'][$question3Id->toString()]['value'])->toBeTrue();
    });

    test('toArray handles empty answers array', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::anonymous();
        $ipAddress = IpAddress::fromString('127.0.0.1');
        $userAgent = UserAgent::fromString('curl/7.68.0');

        $event = new ResponseSubmitted(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            [],
            [],
            CarbonImmutable::now()
        );

        $array = $event->toArray();

        expect($array['answers'])->toBeArray()
            ->and($array['answers'])->toBeEmpty();
    });

    test('toArray handles empty metadata', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::anonymous();
        $ipAddress = IpAddress::fromString('192.168.1.1');
        $userAgent = UserAgent::fromString('Edge/95.0');

        $questionId = QuestionId::fromString(testUuid());
        $answer = Answer::create(
            AnswerId::generate(),
            $questionId,
            AnswerValue::fromMixed('Answer')
        );

        $event = new ResponseSubmitted(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            [$questionId->toString() => $answer],
            [],
            CarbonImmutable::now()
        );

        $array = $event->toArray();

        expect($array['metadata'])->toBeArray()
            ->and($array['metadata'])->toBeEmpty();
    });

    test('toArray preserves complex metadata structures', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::anonymous();
        $ipAddress = IpAddress::fromString('192.168.1.1');
        $userAgent = UserAgent::fromString('Opera/76.0');

        $questionId = QuestionId::fromString(testUuid());
        $answer = Answer::create(
            AnswerId::generate(),
            $questionId,
            AnswerValue::fromMixed('Answer')
        );

        $metadata = [
            'string' => 'value',
            'number' => 42,
            'boolean' => true,
            'array' => ['item1', 'item2'],
            'nested' => [
                'level1' => [
                    'level2' => 'deep value',
                ],
            ],
        ];

        $event = new ResponseSubmitted(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            [$questionId->toString() => $answer],
            $metadata,
            CarbonImmutable::now()
        );

        $array = $event->toArray();

        expect($array['metadata'])->toBe($metadata)
            ->and($array['metadata']['string'])->toBe('value')
            ->and($array['metadata']['number'])->toBe(42)
            ->and($array['metadata']['boolean'])->toBeTrue()
            ->and($array['metadata']['array'])->toBe(['item1', 'item2'])
            ->and($array['metadata']['nested']['level1']['level2'])->toBe('deep value');
    });

    test('occurred at timestamp is set from submitted at', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::anonymous();
        $ipAddress = IpAddress::fromString('192.168.1.1');
        $userAgent = UserAgent::fromString('Mozilla/5.0');

        $questionId = QuestionId::fromString(testUuid());
        $answer = Answer::create(
            AnswerId::generate(),
            $questionId,
            AnswerValue::fromMixed('Answer')
        );

        $submittedAt = CarbonImmutable::parse('2024-07-20 09:15:30');

        $event = new ResponseSubmitted(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            [$questionId->toString() => $answer],
            [],
            $submittedAt
        );

        expect($event->occurredAt())->toEqual($submittedAt);
    });

    test('handles IPv6 address in toArray', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::anonymous();
        $ipAddress = IpAddress::fromString('2001:0db8:85a3:0000:0000:8a2e:0370:7334');
        $userAgent = UserAgent::fromString('Mozilla/5.0');

        $questionId = QuestionId::fromString(testUuid());
        $answer = Answer::create(
            AnswerId::generate(),
            $questionId,
            AnswerValue::fromMixed('Answer')
        );

        $event = new ResponseSubmitted(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            [$questionId->toString() => $answer],
            [],
            CarbonImmutable::now()
        );

        $array = $event->toArray();

        expect($array['ip_address'])->toBe('2001:0db8:85a3:0000:0000:8a2e:0370:7334');
    });

    test('handles long user agent string in toArray', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::anonymous();
        $ipAddress = IpAddress::fromString('192.168.1.1');
        $longUserAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36 Edg/96.0.1054.62';
        $userAgent = UserAgent::fromString($longUserAgent);

        $questionId = QuestionId::fromString(testUuid());
        $answer = Answer::create(
            AnswerId::generate(),
            $questionId,
            AnswerValue::fromMixed('Answer')
        );

        $event = new ResponseSubmitted(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            [$questionId->toString() => $answer],
            [],
            CarbonImmutable::now()
        );

        $array = $event->toArray();

        expect($array['user_agent'])->toBe($longUserAgent);
    });
});
