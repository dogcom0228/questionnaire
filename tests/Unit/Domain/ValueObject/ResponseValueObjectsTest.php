<?php

declare(strict_types=1);

use Liangjin0228\Questionnaire\Domain\Response\Exception\InvalidIpAddressException;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\AnswerId;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\AnswerValue;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\IpAddress;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\Respondent;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\ResponseId;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\UserAgent;

describe('ResponseId Value Object', function () {
    test('can be generated', function () {
        $id = ResponseId::generate();

        expect($id->value())->toBeString()
            ->and(strlen($id->value()))->toBe(36);
    });

    test('can be created from string', function () {
        $uuid = testUuid();
        $id = ResponseId::fromString($uuid);

        expect($id->value())->toBe($uuid);
    });

    test('two ids with same uuid are equal', function () {
        $uuid = testUuid();
        $id1 = ResponseId::fromString($uuid);
        $id2 = ResponseId::fromString($uuid);

        expect($id1->equals($id2))->toBeTrue();
    });

    test('two ids with different uuids are not equal', function () {
        $id1 = ResponseId::generate();
        $id2 = ResponseId::generate();

        expect($id1->equals($id2))->toBeFalse();
    });

    test('string representation returns uuid', function () {
        $uuid = testUuid();
        $id = ResponseId::fromString($uuid);

        expect((string) $id)->toBe($uuid);
    });

    test('json serializes to uuid string', function () {
        $uuid = testUuid();
        $id = ResponseId::fromString($uuid);

        expect($id->jsonSerialize())->toBe($uuid);
    });
});

describe('AnswerId Value Object', function () {
    test('can be generated', function () {
        $id = AnswerId::generate();

        expect($id->value())->toBeString()
            ->and(strlen($id->value()))->toBe(36);
    });

    test('can be created from string', function () {
        $uuid = testUuid();
        $id = AnswerId::fromString($uuid);

        expect($id->value())->toBe($uuid);
    });

    test('two ids with same uuid are equal', function () {
        $uuid = testUuid();
        $id1 = AnswerId::fromString($uuid);
        $id2 = AnswerId::fromString($uuid);

        expect($id1->equals($id2))->toBeTrue();
    });

    test('two ids with different uuids are not equal', function () {
        $id1 = AnswerId::generate();
        $id2 = AnswerId::generate();

        expect($id1->equals($id2))->toBeFalse();
    });

    test('string representation returns uuid', function () {
        $uuid = testUuid();
        $id = AnswerId::fromString($uuid);

        expect((string) $id)->toBe($uuid);
    });

    test('json serializes to uuid string', function () {
        $uuid = testUuid();
        $id = AnswerId::fromString($uuid);

        expect($id->jsonSerialize())->toBe($uuid);
    });
});

describe('AnswerValue Value Object', function () {
    test('can be created from mixed', function () {
        $value = AnswerValue::fromMixed('test');

        expect($value->value())->toBe('test');
    });

    test('can be created from string', function () {
        $value = AnswerValue::fromString('test string');

        expect($value->value())->toBe('test string');
    });

    test('can be created from array', function () {
        $value = AnswerValue::fromArray(['a', 'b', 'c']);

        expect($value->value())->toBe(['a', 'b', 'c']);
    });

    test('can be created from int', function () {
        $value = AnswerValue::fromInt(42);

        expect($value->value())->toBe(42);
    });

    test('can be created from bool', function () {
        $value = AnswerValue::fromBool(true);

        expect($value->value())->toBeTrue();
    });

    test('toMixed returns the value', function () {
        $value = AnswerValue::fromString('test');

        expect($value->toMixed())->toBe('test');
    });

    test('isArray returns true for array value', function () {
        $value = AnswerValue::fromArray(['a', 'b']);

        expect($value->isArray())->toBeTrue();
    });

    test('isArray returns false for non-array value', function () {
        $value = AnswerValue::fromString('test');

        expect($value->isArray())->toBeFalse();
    });

    test('isString returns true for string value', function () {
        $value = AnswerValue::fromString('test');

        expect($value->isString())->toBeTrue();
    });

    test('isString returns false for non-string value', function () {
        $value = AnswerValue::fromInt(42);

        expect($value->isString())->toBeFalse();
    });

    test('isNumeric returns true for numeric value', function () {
        $value = AnswerValue::fromInt(42);

        expect($value->isNumeric())->toBeTrue();
    });

    test('isNumeric returns false for non-numeric value', function () {
        $value = AnswerValue::fromString('abc');

        expect($value->isNumeric())->toBeFalse();
    });

    test('isBool returns true for boolean value', function () {
        $value = AnswerValue::fromBool(false);

        expect($value->isBool())->toBeTrue();
    });

    test('isBool returns false for non-boolean value', function () {
        $value = AnswerValue::fromString('test');

        expect($value->isBool())->toBeFalse();
    });

    test('two values with same content are equal', function () {
        $value1 = AnswerValue::fromString('test');
        $value2 = AnswerValue::fromString('test');

        expect($value1->equals($value2))->toBeTrue();
    });

    test('two values with different content are not equal', function () {
        $value1 = AnswerValue::fromString('test1');
        $value2 = AnswerValue::fromString('test2');

        expect($value1->equals($value2))->toBeFalse();
    });

    test('equals returns false for different value object types', function () {
        $answerValue = AnswerValue::fromString('test');
        $respondent = Respondent::anonymous();

        expect($answerValue->equals($respondent))->toBeFalse();
    });

    test('string representation of array returns json', function () {
        $value = AnswerValue::fromArray(['a', 'b']);

        expect((string) $value)->toBeJson();
    });

    test('string representation of boolean true returns "true"', function () {
        $value = AnswerValue::fromBool(true);

        expect((string) $value)->toBe('true');
    });

    test('string representation of boolean false returns "false"', function () {
        $value = AnswerValue::fromBool(false);

        expect((string) $value)->toBe('false');
    });

    test('string representation of string returns string', function () {
        $value = AnswerValue::fromString('test');

        expect((string) $value)->toBe('test');
    });

    test('string representation of number returns string number', function () {
        $value = AnswerValue::fromInt(42);

        expect((string) $value)->toBe('42');
    });

    test('json serializes to raw value', function () {
        $value = AnswerValue::fromArray(['a', 'b']);

        expect($value->jsonSerialize())->toBe(['a', 'b']);
    });
});

describe('Respondent Value Object', function () {
    test('can be created as anonymous', function () {
        $respondent = Respondent::anonymous();

        expect($respondent->isAnonymous())->toBeTrue()
            ->and($respondent->type())->toBeNull()
            ->and($respondent->id())->toBeNull();
    });

    test('can be created as authenticated', function () {
        $respondent = Respondent::authenticated('user', '123');

        expect($respondent->isAuthenticated())->toBeTrue()
            ->and($respondent->type())->toBe('user')
            ->and($respondent->id())->toBe('123');
    });

    test('isAnonymous returns true for anonymous respondent', function () {
        $respondent = Respondent::anonymous();

        expect($respondent->isAnonymous())->toBeTrue();
    });

    test('isAnonymous returns false for authenticated respondent', function () {
        $respondent = Respondent::authenticated('user', '123');

        expect($respondent->isAnonymous())->toBeFalse();
    });

    test('isAuthenticated returns true for authenticated respondent', function () {
        $respondent = Respondent::authenticated('user', '123');

        expect($respondent->isAuthenticated())->toBeTrue();
    });

    test('isAuthenticated returns false for anonymous respondent', function () {
        $respondent = Respondent::anonymous();

        expect($respondent->isAuthenticated())->toBeFalse();
    });

    test('authenticated method requires both type and id parameters', function () {
        $respondent = Respondent::authenticated('user', '123');

        expect($respondent->type())->toBe('user')
            ->and($respondent->id())->toBe('123');
    });

    test('two anonymous respondents are equal', function () {
        $respondent1 = Respondent::anonymous();
        $respondent2 = Respondent::anonymous();

        expect($respondent1->equals($respondent2))->toBeTrue();
    });

    test('two authenticated respondents with same data are equal', function () {
        $respondent1 = Respondent::authenticated('user', '123');
        $respondent2 = Respondent::authenticated('user', '123');

        expect($respondent1->equals($respondent2))->toBeTrue();
    });

    test('two respondents with different types are not equal', function () {
        $respondent1 = Respondent::authenticated('user', '123');
        $respondent2 = Respondent::authenticated('admin', '123');

        expect($respondent1->equals($respondent2))->toBeFalse();
    });

    test('two respondents with different ids are not equal', function () {
        $respondent1 = Respondent::authenticated('user', '123');
        $respondent2 = Respondent::authenticated('user', '456');

        expect($respondent1->equals($respondent2))->toBeFalse();
    });

    test('anonymous and authenticated respondents are not equal', function () {
        $respondent1 = Respondent::anonymous();
        $respondent2 = Respondent::authenticated('user', '123');

        expect($respondent1->equals($respondent2))->toBeFalse();
    });

    test('equals returns false for different value object types', function () {
        $respondent = Respondent::anonymous();
        $answerId = AnswerId::generate();

        expect($respondent->equals($answerId))->toBeFalse();
    });

    test('value returns array with type and id', function () {
        $respondent = Respondent::authenticated('user', '123');

        expect($respondent->value())->toBeArray()
            ->and($respondent->value())->toHaveKey('type')
            ->and($respondent->value())->toHaveKey('id')
            ->and($respondent->value()['type'])->toBe('user')
            ->and($respondent->value()['id'])->toBe('123');
    });

    test('value returns array with nulls for anonymous', function () {
        $respondent = Respondent::anonymous();

        $value = $respondent->value();

        expect($value['type'])->toBeNull()
            ->and($value['id'])->toBeNull();
    });

    test('string representation of anonymous returns "anonymous"', function () {
        $respondent = Respondent::anonymous();

        expect((string) $respondent)->toBe('anonymous');
    });

    test('string representation of authenticated returns type:id format', function () {
        $respondent = Respondent::authenticated('user', '123');

        expect((string) $respondent)->toBe('user:123');
    });

    test('json serializes to value array', function () {
        $respondent = Respondent::authenticated('user', '123');

        expect($respondent->jsonSerialize())->toBe($respondent->value());
    });
});

describe('IpAddress Value Object', function () {
    test('can be created from valid IPv4 address', function () {
        $ip = IpAddress::fromString('192.168.1.1');

        expect($ip->value())->toBe('192.168.1.1');
    });

    test('can be created from valid IPv6 address', function () {
        $ip = IpAddress::fromString('2001:0db8:85a3:0000:0000:8a2e:0370:7334');

        expect($ip->value())->toBe('2001:0db8:85a3:0000:0000:8a2e:0370:7334');
    });

    test('throws exception for invalid IP address', function () {
        IpAddress::fromString('999.999.999.999');
    })->throws(InvalidIpAddressException::class);

    test('throws exception for non-IP string', function () {
        IpAddress::fromString('not-an-ip');
    })->throws(InvalidIpAddressException::class);

    test('throws exception for empty string', function () {
        IpAddress::fromString('');
    })->throws(InvalidIpAddressException::class);

    test('isIpv4 returns true for IPv4 address', function () {
        $ip = IpAddress::fromString('192.168.1.1');

        expect($ip->isIpv4())->toBeTrue();
    });

    test('isIpv4 returns false for IPv6 address', function () {
        $ip = IpAddress::fromString('::1');

        expect($ip->isIpv4())->toBeFalse();
    });

    test('isIpv6 returns true for IPv6 address', function () {
        $ip = IpAddress::fromString('::1');

        expect($ip->isIpv6())->toBeTrue();
    });

    test('isIpv6 returns false for IPv4 address', function () {
        $ip = IpAddress::fromString('127.0.0.1');

        expect($ip->isIpv6())->toBeFalse();
    });

    test('two addresses with same value are equal', function () {
        $ip1 = IpAddress::fromString('192.168.1.1');
        $ip2 = IpAddress::fromString('192.168.1.1');

        expect($ip1->equals($ip2))->toBeTrue();
    });

    test('two addresses with different values are not equal', function () {
        $ip1 = IpAddress::fromString('192.168.1.1');
        $ip2 = IpAddress::fromString('192.168.1.2');

        expect($ip1->equals($ip2))->toBeFalse();
    });

    test('equals returns false for different value object types', function () {
        $ip = IpAddress::fromString('192.168.1.1');
        $userAgent = UserAgent::fromString('Mozilla/5.0');

        expect($ip->equals($userAgent))->toBeFalse();
    });

    test('toString returns IP address', function () {
        $ip = IpAddress::fromString('192.168.1.1');

        expect($ip->toString())->toBe('192.168.1.1');
    });

    test('string representation returns IP address', function () {
        $ip = IpAddress::fromString('192.168.1.1');

        expect((string) $ip)->toBe('192.168.1.1');
    });

    test('json serializes to IP string', function () {
        $ip = IpAddress::fromString('192.168.1.1');

        expect($ip->jsonSerialize())->toBe('192.168.1.1');
    });
});

describe('UserAgent Value Object', function () {
    test('can be created from string', function () {
        $ua = UserAgent::fromString('Mozilla/5.0');

        expect($ua->value())->toBe('Mozilla/5.0');
    });

    test('can be created from empty string', function () {
        $ua = UserAgent::fromString('');

        expect($ua->value())->toBe('');
    });

    test('can be created from long user agent string', function () {
        $longUa = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
        $ua = UserAgent::fromString($longUa);

        expect($ua->value())->toBe($longUa);
    });

    test('two user agents with same value are equal', function () {
        $ua1 = UserAgent::fromString('Mozilla/5.0');
        $ua2 = UserAgent::fromString('Mozilla/5.0');

        expect($ua1->equals($ua2))->toBeTrue();
    });

    test('two user agents with different values are not equal', function () {
        $ua1 = UserAgent::fromString('Mozilla/5.0');
        $ua2 = UserAgent::fromString('Chrome/91.0');

        expect($ua1->equals($ua2))->toBeFalse();
    });

    test('equals returns false for different value object types', function () {
        $ua = UserAgent::fromString('Mozilla/5.0');
        $ip = IpAddress::fromString('192.168.1.1');

        expect($ua->equals($ip))->toBeFalse();
    });

    test('toString returns user agent string', function () {
        $ua = UserAgent::fromString('Mozilla/5.0');

        expect($ua->toString())->toBe('Mozilla/5.0');
    });

    test('string representation returns user agent string', function () {
        $ua = UserAgent::fromString('Mozilla/5.0');

        expect((string) $ua)->toBe('Mozilla/5.0');
    });

    test('json serializes to user agent string', function () {
        $ua = UserAgent::fromString('Mozilla/5.0');

        expect($ua->jsonSerialize())->toBe('Mozilla/5.0');
    });
});
