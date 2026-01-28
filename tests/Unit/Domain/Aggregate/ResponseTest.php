<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Response\Aggregate\Response;
use Liangjin0228\Questionnaire\Domain\Response\Entity\Answer;
use Liangjin0228\Questionnaire\Domain\Response\Event\ResponseSubmitted;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\AnswerId;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\AnswerValue;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\IpAddress;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\Respondent;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\ResponseId;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\UserAgent;

describe('Response Aggregate - Submission', function () {
    test('submits response and records ResponseSubmitted event', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::anonymous();
        $ipAddress = IpAddress::fromString('192.168.1.100');
        $userAgent = UserAgent::fromString('Mozilla/5.0');

        $question1Id = QuestionId::fromString(testUuid());
        $answer1 = Answer::create(
            AnswerId::generate(),
            $question1Id,
            AnswerValue::fromMixed('Answer text')
        );

        $answers = [$question1Id->toString() => $answer1];
        $metadata = ['browser' => 'chrome', 'os' => 'linux'];

        $response = Response::submit(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            $answers,
            $metadata
        );

        expect($response)->toBeInstanceOf(Response::class)
            ->and($response->recordedEvents())->toHaveCount(1)
            ->and($response->recordedEvents()[0])->toBeInstanceOf(ResponseSubmitted::class);
    });

    test('applies ResponseSubmitted event and sets initial state', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::authenticated('user', '123');
        $ipAddress = IpAddress::fromString('10.0.0.1');
        $userAgent = UserAgent::fromString('Mozilla/5.0 (Windows NT 10.0; Win64; x64)');

        $question1Id = QuestionId::fromString(testUuid());
        $question2Id = QuestionId::fromString(testUuid());

        $answer1 = Answer::create(
            AnswerId::generate(),
            $question1Id,
            AnswerValue::fromMixed('First answer')
        );

        $answer2 = Answer::create(
            AnswerId::generate(),
            $question2Id,
            AnswerValue::fromMixed(['option1', 'option2'])
        );

        $answers = [
            $question1Id->toString() => $answer1,
            $question2Id->toString() => $answer2,
        ];

        $metadata = ['referrer' => 'google.com', 'device' => 'desktop'];

        $response = Response::submit(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            $answers,
            $metadata
        );

        expect($response->id())->toEqual($responseId)
            ->and($response->questionnaireId())->toEqual($questionnaireId)
            ->and($response->respondent())->toEqual($respondent)
            ->and($response->ipAddress())->toEqual($ipAddress)
            ->and($response->userAgent())->toEqual($userAgent)
            ->and($response->answers())->toBe($answers)
            ->and($response->metadata())->toBe($metadata)
            ->and($response->submittedAt())->toBeInstanceOf(CarbonImmutable::class);
    });

    test('submits response with empty metadata', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::anonymous();
        $ipAddress = IpAddress::fromString('127.0.0.1');
        $userAgent = UserAgent::fromString('curl/7.68.0');

        $questionId = QuestionId::fromString(testUuid());
        $answer = Answer::create(
            AnswerId::generate(),
            $questionId,
            AnswerValue::fromMixed('Answer')
        );

        $answers = [$questionId->toString() => $answer];

        $response = Response::submit(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            $answers
        );

        expect($response->metadata())->toBeEmpty();
    });
});

describe('Response Aggregate - Answer Management', function () {
    test('can retrieve answer by question id', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::anonymous();
        $ipAddress = IpAddress::fromString('192.168.1.1');
        $userAgent = UserAgent::fromString('Safari/537.36');

        $question1Id = QuestionId::fromString(testUuid());
        $question2Id = QuestionId::fromString(testUuid());

        $answer1 = Answer::create(
            AnswerId::generate(),
            $question1Id,
            AnswerValue::fromMixed('First answer')
        );

        $answer2 = Answer::create(
            AnswerId::generate(),
            $question2Id,
            AnswerValue::fromMixed(42)
        );

        $answers = [
            $question1Id->toString() => $answer1,
            $question2Id->toString() => $answer2,
        ];

        $response = Response::submit(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            $answers
        );

        expect($response->getAnswer($question1Id))->toBe($answer1)
            ->and($response->getAnswer($question2Id))->toBe($answer2);
    });

    test('returns null when answer does not exist', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::anonymous();
        $ipAddress = IpAddress::fromString('192.168.1.1');
        $userAgent = UserAgent::fromString('Firefox/91.0');

        $questionId = QuestionId::fromString(testUuid());
        $answer = Answer::create(
            AnswerId::generate(),
            $questionId,
            AnswerValue::fromMixed('Answer')
        );

        $answers = [$questionId->toString() => $answer];

        $response = Response::submit(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            $answers
        );

        $nonExistentQuestionId = QuestionId::fromString(testUuid());

        expect($response->getAnswer($nonExistentQuestionId))->toBeNull();
    });

    test('can check if answer exists for question', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::anonymous();
        $ipAddress = IpAddress::fromString('192.168.1.1');
        $userAgent = UserAgent::fromString('Edge/95.0');

        $question1Id = QuestionId::fromString(testUuid());
        $question2Id = QuestionId::fromString(testUuid());

        $answer1 = Answer::create(
            AnswerId::generate(),
            $question1Id,
            AnswerValue::fromMixed('Answer')
        );

        $answers = [$question1Id->toString() => $answer1];

        $response = Response::submit(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            $answers
        );

        expect($response->hasAnswer($question1Id))->toBeTrue()
            ->and($response->hasAnswer($question2Id))->toBeFalse();
    });

    test('answers collection preserves question id keys', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::anonymous();
        $ipAddress = IpAddress::fromString('192.168.1.1');
        $userAgent = UserAgent::fromString('Opera/76.0');

        $question1Id = QuestionId::fromString(testUuid());
        $question2Id = QuestionId::fromString(testUuid());
        $question3Id = QuestionId::fromString(testUuid());

        $answer1 = Answer::create(AnswerId::generate(), $question1Id, AnswerValue::fromMixed('A1'));
        $answer2 = Answer::create(AnswerId::generate(), $question2Id, AnswerValue::fromMixed('A2'));
        $answer3 = Answer::create(AnswerId::generate(), $question3Id, AnswerValue::fromMixed('A3'));

        $answers = [
            $question1Id->toString() => $answer1,
            $question2Id->toString() => $answer2,
            $question3Id->toString() => $answer3,
        ];

        $response = Response::submit(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            $answers
        );

        $retrievedAnswers = $response->answers();

        expect($retrievedAnswers)->toHaveKey($question1Id->toString())
            ->and($retrievedAnswers)->toHaveKey($question2Id->toString())
            ->and($retrievedAnswers)->toHaveKey($question3Id->toString())
            ->and($retrievedAnswers[$question1Id->toString()])->toBe($answer1)
            ->and($retrievedAnswers[$question2Id->toString()])->toBe($answer2)
            ->and($retrievedAnswers[$question3Id->toString()])->toBe($answer3);
    });
});

describe('Response Aggregate - Respondent Handling', function () {
    test('isAuthenticated returns true for authenticated respondent', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::authenticated('user', 'user-123');
        $ipAddress = IpAddress::fromString('192.168.1.1');
        $userAgent = UserAgent::fromString('Chrome/96.0');

        $questionId = QuestionId::fromString(testUuid());
        $answer = Answer::create(
            AnswerId::generate(),
            $questionId,
            AnswerValue::fromMixed('Answer')
        );

        $response = Response::submit(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            [$questionId->toString() => $answer]
        );

        expect($response->isAuthenticated())->toBeTrue()
            ->and($response->respondent()->isAuthenticated())->toBeTrue()
            ->and($response->respondent()->type())->toBe('user')
            ->and($response->respondent()->id())->toBe('user-123');
    });

    test('isAuthenticated returns false for anonymous respondent', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::anonymous();
        $ipAddress = IpAddress::fromString('192.168.1.1');
        $userAgent = UserAgent::fromString('Chrome/96.0');

        $questionId = QuestionId::fromString(testUuid());
        $answer = Answer::create(
            AnswerId::generate(),
            $questionId,
            AnswerValue::fromMixed('Answer')
        );

        $response = Response::submit(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            [$questionId->toString() => $answer]
        );

        expect($response->isAuthenticated())->toBeFalse()
            ->and($response->respondent()->isAnonymous())->toBeTrue();
    });
});

describe('Response Aggregate - IP and User Agent', function () {
    test('stores IPv4 address correctly', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::anonymous();
        $ipAddress = IpAddress::fromString('203.0.113.45');
        $userAgent = UserAgent::fromString('Mozilla/5.0');

        $questionId = QuestionId::fromString(testUuid());
        $answer = Answer::create(
            AnswerId::generate(),
            $questionId,
            AnswerValue::fromMixed('Answer')
        );

        $response = Response::submit(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            [$questionId->toString() => $answer]
        );

        expect($response->ipAddress()->toString())->toBe('203.0.113.45')
            ->and($response->ipAddress()->isIpv4())->toBeTrue()
            ->and($response->ipAddress()->isIpv6())->toBeFalse();
    });

    test('stores IPv6 address correctly', function () {
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

        $response = Response::submit(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            [$questionId->toString() => $answer]
        );

        expect($response->ipAddress()->toString())->toBe('2001:0db8:85a3:0000:0000:8a2e:0370:7334')
            ->and($response->ipAddress()->isIpv6())->toBeTrue()
            ->and($response->ipAddress()->isIpv4())->toBeFalse();
    });

    test('stores user agent string correctly', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::anonymous();
        $ipAddress = IpAddress::fromString('192.168.1.1');
        $userAgentString = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36';
        $userAgent = UserAgent::fromString($userAgentString);

        $questionId = QuestionId::fromString(testUuid());
        $answer = Answer::create(
            AnswerId::generate(),
            $questionId,
            AnswerValue::fromMixed('Answer')
        );

        $response = Response::submit(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            [$questionId->toString() => $answer]
        );

        expect($response->userAgent()->toString())->toBe($userAgentString);
    });
});

describe('Response Aggregate - Metadata Handling', function () {
    test('stores metadata with various types', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::anonymous();
        $ipAddress = IpAddress::fromString('192.168.1.1');
        $userAgent = UserAgent::fromString('Mozilla/5.0');

        $metadata = [
            'string_value' => 'test',
            'int_value' => 42,
            'bool_value' => true,
            'array_value' => ['item1', 'item2'],
            'nested' => [
                'key1' => 'value1',
                'key2' => 123,
            ],
        ];

        $questionId = QuestionId::fromString(testUuid());
        $answer = Answer::create(
            AnswerId::generate(),
            $questionId,
            AnswerValue::fromMixed('Answer')
        );

        $response = Response::submit(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            [$questionId->toString() => $answer],
            $metadata
        );

        expect($response->metadata())->toBe($metadata)
            ->and($response->metadata()['string_value'])->toBe('test')
            ->and($response->metadata()['int_value'])->toBe(42)
            ->and($response->metadata()['bool_value'])->toBeTrue()
            ->and($response->metadata()['array_value'])->toBe(['item1', 'item2'])
            ->and($response->metadata()['nested'])->toBe(['key1' => 'value1', 'key2' => 123]);
    });

    test('metadata is empty array when not provided', function () {
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

        $response = Response::submit(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            [$questionId->toString() => $answer]
        );

        expect($response->metadata())->toBeEmpty()
            ->and($response->metadata())->toBeArray();
    });
});

describe('Response Aggregate - Getters', function () {
    test('getAggregateRootId returns UUID interface', function () {
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

        $response = Response::submit(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            [$questionId->toString() => $answer]
        );

        expect($response->getAggregateRootId())->toBeInstanceOf(\Ramsey\Uuid\UuidInterface::class)
            ->and($response->getAggregateRootId()->toString())->toBe($responseId->toString());
    });

    test('submittedAt returns timestamp', function () {
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

        $beforeSubmit = CarbonImmutable::now();

        $response = Response::submit(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            [$questionId->toString() => $answer]
        );

        $afterSubmit = CarbonImmutable::now();

        expect($response->submittedAt())->toBeInstanceOf(CarbonImmutable::class)
            ->and($response->submittedAt()->greaterThanOrEqualTo($beforeSubmit))->toBeTrue()
            ->and($response->submittedAt()->lessThanOrEqualTo($afterSubmit))->toBeTrue();
    });

    test('all getters return correct values', function () {
        $responseId = ResponseId::generate();
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $respondent = Respondent::authenticated('admin', 'admin-456');
        $ipAddress = IpAddress::fromString('10.20.30.40');
        $userAgent = UserAgent::fromString('TestAgent/1.0');

        $questionId = QuestionId::fromString(testUuid());
        $answer = Answer::create(
            AnswerId::generate(),
            $questionId,
            AnswerValue::fromMixed('Test Answer')
        );

        $answers = [$questionId->toString() => $answer];
        $metadata = ['test' => 'data'];

        $response = Response::submit(
            $responseId,
            $questionnaireId,
            $respondent,
            $ipAddress,
            $userAgent,
            $answers,
            $metadata
        );

        expect($response->id())->toBe($responseId)
            ->and($response->questionnaireId())->toBe($questionnaireId)
            ->and($response->respondent())->toBe($respondent)
            ->and($response->ipAddress())->toBe($ipAddress)
            ->and($response->userAgent())->toBe($userAgent)
            ->and($response->answers())->toBe($answers)
            ->and($response->metadata())->toBe($metadata)
            ->and($response->submittedAt())->toBeInstanceOf(CarbonImmutable::class);
    });
});
