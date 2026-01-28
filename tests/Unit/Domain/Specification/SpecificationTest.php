<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Entity\Question;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Specification\QuestionnaireAcceptsModificationsSpecification;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Specification\QuestionnaireCanBePublishedSpecification;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Specification\QuestionnaireHasQuestionsSpecification;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Specification\QuestionnaireIsActiveSpecification;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Specification\QuestionnaireIsPublishedSpecification;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\DateRange;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSettings;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSlug;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionOptions;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionText;
use Liangjin0228\Questionnaire\Domain\Response\Aggregate\Response;
use Liangjin0228\Questionnaire\Domain\Response\Entity\Answer;
use Liangjin0228\Questionnaire\Domain\Response\Specification\ResponseHasAnswerForQuestionSpecification;
use Liangjin0228\Questionnaire\Domain\Response\Specification\ResponseIsAuthenticatedSpecification;
use Liangjin0228\Questionnaire\Domain\Response\Specification\ResponseIsCompleteSpecification;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\AnswerId;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\AnswerValue;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\IpAddress;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\Respondent;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\ResponseId;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\UserAgent;

describe('QuestionnaireIsActiveSpecification', function () {
    test('is satisfied when questionnaire is published and within date range', function () {
        $questionnaire = Questionnaire::create(
            QuestionnaireId::fromString(testUuid()),
            QuestionnaireTitle::fromString('Active Survey'),
            QuestionnaireSlug::fromString('active-survey'),
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

        $spec = new QuestionnaireIsActiveSpecification(CarbonImmutable::parse('2024-06-15'));

        expect($spec->isSatisfiedBy($questionnaire))->toBeTrue();
    });

    test('is not satisfied when questionnaire is not published', function () {
        $questionnaire = Questionnaire::create(
            QuestionnaireId::fromString(testUuid()),
            QuestionnaireTitle::fromString('Draft Survey'),
            QuestionnaireSlug::fromString('draft-survey'),
            null,
            DateRange::create(
                CarbonImmutable::parse('2024-01-01'),
                CarbonImmutable::parse('2024-12-31')
            ),
            QuestionnaireSettings::default()
        );

        $spec = new QuestionnaireIsActiveSpecification(CarbonImmutable::parse('2024-06-15'));

        expect($spec->isSatisfiedBy($questionnaire))->toBeFalse();
    });

    test('is not satisfied when outside date range', function () {
        $questionnaire = Questionnaire::create(
            QuestionnaireId::fromString(testUuid()),
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

        $spec = new QuestionnaireIsActiveSpecification(CarbonImmutable::parse('2025-01-15'));

        expect($spec->isSatisfiedBy($questionnaire))->toBeFalse();
    });

    test('is not satisfied with non-questionnaire candidate', function () {
        $spec = new QuestionnaireIsActiveSpecification;

        expect($spec->isSatisfiedBy('not a questionnaire'))->toBeFalse()
            ->and($spec->isSatisfiedBy(null))->toBeFalse()
            ->and($spec->isSatisfiedBy(123))->toBeFalse();
    });
});

describe('QuestionnaireIsPublishedSpecification', function () {
    test('is satisfied when questionnaire is published', function () {
        $questionnaire = Questionnaire::create(
            QuestionnaireId::fromString(testUuid()),
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::unlimited(),
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

        $spec = new QuestionnaireIsPublishedSpecification;

        expect($spec->isSatisfiedBy($questionnaire))->toBeTrue();
    });

    test('is not satisfied when questionnaire is draft', function () {
        $questionnaire = Questionnaire::create(
            QuestionnaireId::fromString(testUuid()),
            QuestionnaireTitle::fromString('Draft Survey'),
            QuestionnaireSlug::fromString('draft-survey'),
            null,
            DateRange::unlimited(),
            QuestionnaireSettings::default()
        );

        $spec = new QuestionnaireIsPublishedSpecification;

        expect($spec->isSatisfiedBy($questionnaire))->toBeFalse();
    });

    test('is not satisfied when questionnaire is closed', function () {
        $questionnaire = Questionnaire::create(
            QuestionnaireId::fromString(testUuid()),
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::unlimited(),
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

        $spec = new QuestionnaireIsPublishedSpecification;

        expect($spec->isSatisfiedBy($questionnaire))->toBeFalse();
    });

    test('is not satisfied with non-questionnaire candidate', function () {
        $spec = new QuestionnaireIsPublishedSpecification;

        expect($spec->isSatisfiedBy('not a questionnaire'))->toBeFalse();
    });
});

describe('QuestionnaireHasQuestionsSpecification', function () {
    test('is satisfied when questionnaire has questions', function () {
        $questionnaire = Questionnaire::create(
            QuestionnaireId::fromString(testUuid()),
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::unlimited(),
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

        $spec = new QuestionnaireHasQuestionsSpecification;

        expect($spec->isSatisfiedBy($questionnaire))->toBeTrue();
    });

    test('is not satisfied when questionnaire has no questions', function () {
        $questionnaire = Questionnaire::create(
            QuestionnaireId::fromString(testUuid()),
            QuestionnaireTitle::fromString('Empty Survey'),
            QuestionnaireSlug::fromString('empty-survey'),
            null,
            DateRange::unlimited(),
            QuestionnaireSettings::default()
        );

        $spec = new QuestionnaireHasQuestionsSpecification;

        expect($spec->isSatisfiedBy($questionnaire))->toBeFalse();
    });

    test('is not satisfied with non-questionnaire candidate', function () {
        $spec = new QuestionnaireHasQuestionsSpecification;

        expect($spec->isSatisfiedBy([]))->toBeFalse();
    });
});

describe('QuestionnaireCanBePublishedSpecification', function () {
    test('is satisfied when questionnaire is draft and has questions', function () {
        $questionnaire = Questionnaire::create(
            QuestionnaireId::fromString(testUuid()),
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::unlimited(),
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

        $spec = new QuestionnaireCanBePublishedSpecification;

        expect($spec->isSatisfiedBy($questionnaire))->toBeTrue();
    });

    test('is not satisfied when questionnaire has no questions', function () {
        $questionnaire = Questionnaire::create(
            QuestionnaireId::fromString(testUuid()),
            QuestionnaireTitle::fromString('Empty Survey'),
            QuestionnaireSlug::fromString('empty-survey'),
            null,
            DateRange::unlimited(),
            QuestionnaireSettings::default()
        );

        $spec = new QuestionnaireCanBePublishedSpecification;

        expect($spec->isSatisfiedBy($questionnaire))->toBeFalse();
    });

    test('is not satisfied when questionnaire is already published', function () {
        $questionnaire = Questionnaire::create(
            QuestionnaireId::fromString(testUuid()),
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::unlimited(),
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

        $spec = new QuestionnaireCanBePublishedSpecification;

        expect($spec->isSatisfiedBy($questionnaire))->toBeFalse();
    });

    test('is not satisfied with non-questionnaire candidate', function () {
        $spec = new QuestionnaireCanBePublishedSpecification;

        expect($spec->isSatisfiedBy(new stdClass))->toBeFalse();
    });
});

describe('QuestionnaireAcceptsModificationsSpecification', function () {
    test('is satisfied when questionnaire is in draft status', function () {
        $questionnaire = Questionnaire::create(
            QuestionnaireId::fromString(testUuid()),
            QuestionnaireTitle::fromString('Draft Survey'),
            QuestionnaireSlug::fromString('draft-survey'),
            null,
            DateRange::unlimited(),
            QuestionnaireSettings::default()
        );

        $spec = new QuestionnaireAcceptsModificationsSpecification;

        expect($spec->isSatisfiedBy($questionnaire))->toBeTrue();
    });

    test('is not satisfied when questionnaire is published', function () {
        $questionnaire = Questionnaire::create(
            QuestionnaireId::fromString(testUuid()),
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::unlimited(),
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

        $spec = new QuestionnaireAcceptsModificationsSpecification;

        expect($spec->isSatisfiedBy($questionnaire))->toBeFalse();
    });

    test('is not satisfied with non-questionnaire candidate', function () {
        $spec = new QuestionnaireAcceptsModificationsSpecification;

        expect($spec->isSatisfiedBy(true))->toBeFalse();
    });
});

describe('ResponseIsCompleteSpecification', function () {
    test('is satisfied when response has answers for all questions', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $questionnaireId,
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::unlimited(),
            QuestionnaireSettings::default()
        );

        $question1Id = QuestionId::fromString(testUuid());
        $question2Id = QuestionId::fromString(testUuid());

        $question1 = Question::create($question1Id, QuestionText::fromString('Q1'), 'text', QuestionOptions::fromArray([]), true, 1, null, []);
        $question2 = Question::create($question2Id, QuestionText::fromString('Q2'), 'text', QuestionOptions::fromArray([]), true, 2, null, []);

        $questionnaire->addQuestion($question1);
        $questionnaire->addQuestion($question2);

        $answer1 = Answer::create(AnswerId::generate(), $question1Id, AnswerValue::fromMixed('A1'));
        $answer2 = Answer::create(AnswerId::generate(), $question2Id, AnswerValue::fromMixed('A2'));

        $response = Response::submit(
            ResponseId::generate(),
            $questionnaireId,
            Respondent::anonymous(),
            IpAddress::fromString('192.168.1.1'),
            UserAgent::fromString('Mozilla/5.0'),
            [
                $question1Id->toString() => $answer1,
                $question2Id->toString() => $answer2,
            ]
        );

        $spec = new ResponseIsCompleteSpecification($questionnaire);

        expect($spec->isSatisfiedBy($response))->toBeTrue();
    });

    test('is not satisfied when response is missing answers', function () {
        $questionnaireId = QuestionnaireId::fromString(testUuid());
        $questionnaire = Questionnaire::create(
            $questionnaireId,
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::unlimited(),
            QuestionnaireSettings::default()
        );

        $question1Id = QuestionId::fromString(testUuid());
        $question2Id = QuestionId::fromString(testUuid());

        $question1 = Question::create($question1Id, QuestionText::fromString('Q1'), 'text', QuestionOptions::fromArray([]), true, 1, null, []);
        $question2 = Question::create($question2Id, QuestionText::fromString('Q2'), 'text', QuestionOptions::fromArray([]), true, 2, null, []);

        $questionnaire->addQuestion($question1);
        $questionnaire->addQuestion($question2);

        $answer1 = Answer::create(AnswerId::generate(), $question1Id, AnswerValue::fromMixed('A1'));

        $response = Response::submit(
            ResponseId::generate(),
            $questionnaireId,
            Respondent::anonymous(),
            IpAddress::fromString('192.168.1.1'),
            UserAgent::fromString('Mozilla/5.0'),
            [$question1Id->toString() => $answer1]
        );

        $spec = new ResponseIsCompleteSpecification($questionnaire);

        expect($spec->isSatisfiedBy($response))->toBeFalse();
    });

    test('is not satisfied when response is for different questionnaire', function () {
        $questionnaire1Id = QuestionnaireId::fromString(testUuid());
        $questionnaire2Id = QuestionnaireId::fromString(testUuid());

        $questionnaire = Questionnaire::create(
            $questionnaire1Id,
            QuestionnaireTitle::fromString('Survey 1'),
            QuestionnaireSlug::fromString('survey-1'),
            null,
            DateRange::unlimited(),
            QuestionnaireSettings::default()
        );

        $question = Question::create(
            QuestionId::fromString(testUuid()),
            QuestionText::fromString('Q1'),
            'text',
            QuestionOptions::fromArray([]),
            true,
            1,
            null,
            []
        );

        $questionnaire->addQuestion($question);

        $response = Response::submit(
            ResponseId::generate(),
            $questionnaire2Id,
            Respondent::anonymous(),
            IpAddress::fromString('192.168.1.1'),
            UserAgent::fromString('Mozilla/5.0'),
            []
        );

        $spec = new ResponseIsCompleteSpecification($questionnaire);

        expect($spec->isSatisfiedBy($response))->toBeFalse();
    });

    test('is not satisfied with non-response candidate', function () {
        $questionnaire = Questionnaire::create(
            QuestionnaireId::fromString(testUuid()),
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::unlimited(),
            QuestionnaireSettings::default()
        );

        $spec = new ResponseIsCompleteSpecification($questionnaire);

        expect($spec->isSatisfiedBy('not a response'))->toBeFalse();
    });
});

describe('ResponseIsAuthenticatedSpecification', function () {
    test('is satisfied when response has authenticated respondent', function () {
        $response = Response::submit(
            ResponseId::generate(),
            QuestionnaireId::fromString(testUuid()),
            Respondent::authenticated('user', 'user-123'),
            IpAddress::fromString('192.168.1.1'),
            UserAgent::fromString('Mozilla/5.0'),
            []
        );

        $spec = new ResponseIsAuthenticatedSpecification;

        expect($spec->isSatisfiedBy($response))->toBeTrue();
    });

    test('is not satisfied when response has anonymous respondent', function () {
        $response = Response::submit(
            ResponseId::generate(),
            QuestionnaireId::fromString(testUuid()),
            Respondent::anonymous(),
            IpAddress::fromString('192.168.1.1'),
            UserAgent::fromString('Mozilla/5.0'),
            []
        );

        $spec = new ResponseIsAuthenticatedSpecification;

        expect($spec->isSatisfiedBy($response))->toBeFalse();
    });

    test('is not satisfied with non-response candidate', function () {
        $spec = new ResponseIsAuthenticatedSpecification;

        expect($spec->isSatisfiedBy(null))->toBeFalse();
    });
});

describe('ResponseHasAnswerForQuestionSpecification', function () {
    test('is satisfied when response has answer for question', function () {
        $questionId = QuestionId::fromString(testUuid());
        $answer = Answer::create(
            AnswerId::generate(),
            $questionId,
            AnswerValue::fromMixed('Answer')
        );

        $response = Response::submit(
            ResponseId::generate(),
            QuestionnaireId::fromString(testUuid()),
            Respondent::anonymous(),
            IpAddress::fromString('192.168.1.1'),
            UserAgent::fromString('Mozilla/5.0'),
            [$questionId->toString() => $answer]
        );

        $spec = new ResponseHasAnswerForQuestionSpecification($questionId);

        expect($spec->isSatisfiedBy($response))->toBeTrue();
    });

    test('is not satisfied when response does not have answer for question', function () {
        $question1Id = QuestionId::fromString(testUuid());
        $question2Id = QuestionId::fromString(testUuid());

        $answer = Answer::create(
            AnswerId::generate(),
            $question1Id,
            AnswerValue::fromMixed('Answer')
        );

        $response = Response::submit(
            ResponseId::generate(),
            QuestionnaireId::fromString(testUuid()),
            Respondent::anonymous(),
            IpAddress::fromString('192.168.1.1'),
            UserAgent::fromString('Mozilla/5.0'),
            [$question1Id->toString() => $answer]
        );

        $spec = new ResponseHasAnswerForQuestionSpecification($question2Id);

        expect($spec->isSatisfiedBy($response))->toBeFalse();
    });

    test('is not satisfied with non-response candidate', function () {
        $spec = new ResponseHasAnswerForQuestionSpecification(QuestionId::fromString(testUuid()));

        expect($spec->isSatisfiedBy([]))->toBeFalse();
    });
});

describe('Composite Specifications', function () {
    test('and specification combines two specifications', function () {
        $questionnaire = Questionnaire::create(
            QuestionnaireId::fromString(testUuid()),
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::unlimited(),
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

        $hasQuestionsSpec = new QuestionnaireHasQuestionsSpecification;
        $acceptsModsSpec = new QuestionnaireAcceptsModificationsSpecification;

        $compositeSpec = $hasQuestionsSpec->and($acceptsModsSpec);

        expect($compositeSpec->isSatisfiedBy($questionnaire))->toBeTrue();
    });

    test('and specification fails if one specification fails', function () {
        $questionnaire = Questionnaire::create(
            QuestionnaireId::fromString(testUuid()),
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::unlimited(),
            QuestionnaireSettings::default()
        );

        $hasQuestionsSpec = new QuestionnaireHasQuestionsSpecification;
        $acceptsModsSpec = new QuestionnaireAcceptsModificationsSpecification;

        $compositeSpec = $hasQuestionsSpec->and($acceptsModsSpec);

        expect($compositeSpec->isSatisfiedBy($questionnaire))->toBeFalse();
    });

    test('or specification succeeds if one specification succeeds', function () {
        $questionnaire = Questionnaire::create(
            QuestionnaireId::fromString(testUuid()),
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::unlimited(),
            QuestionnaireSettings::default()
        );

        $hasQuestionsSpec = new QuestionnaireHasQuestionsSpecification;
        $acceptsModsSpec = new QuestionnaireAcceptsModificationsSpecification;

        $compositeSpec = $hasQuestionsSpec->or($acceptsModsSpec);

        expect($compositeSpec->isSatisfiedBy($questionnaire))->toBeTrue();
    });

    test('or specification fails if both specifications fail', function () {
        $questionnaire = Questionnaire::create(
            QuestionnaireId::fromString(testUuid()),
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::unlimited(),
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

        $hasQuestionsSpec = new QuestionnaireHasQuestionsSpecification;
        $isPublishedSpec = new QuestionnaireIsPublishedSpecification;

        $compositeSpec = $hasQuestionsSpec->and($isPublishedSpec)->not();

        expect($compositeSpec->isSatisfiedBy($questionnaire))->toBeFalse();
    });

    test('not specification inverts the result', function () {
        $questionnaire = Questionnaire::create(
            QuestionnaireId::fromString(testUuid()),
            QuestionnaireTitle::fromString('Draft Survey'),
            QuestionnaireSlug::fromString('draft-survey'),
            null,
            DateRange::unlimited(),
            QuestionnaireSettings::default()
        );

        $isPublishedSpec = new QuestionnaireIsPublishedSpecification;

        $notPublishedSpec = $isPublishedSpec->not();

        expect($notPublishedSpec->isSatisfiedBy($questionnaire))->toBeTrue();
    });

    test('complex composite specification', function () {
        $questionnaire = Questionnaire::create(
            QuestionnaireId::fromString(testUuid()),
            QuestionnaireTitle::fromString('Survey'),
            QuestionnaireSlug::fromString('survey'),
            null,
            DateRange::unlimited(),
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

        $hasQuestionsSpec = new QuestionnaireHasQuestionsSpecification;
        $isPublishedSpec = new QuestionnaireIsPublishedSpecification;
        $acceptsModsSpec = new QuestionnaireAcceptsModificationsSpecification;

        $hasQuestionsAndAcceptsModifications = $hasQuestionsSpec->and($acceptsModsSpec);
        $compositeSpec = $hasQuestionsAndAcceptsModifications->or($isPublishedSpec);

        expect($compositeSpec->isSatisfiedBy($questionnaire))->toBeTrue();
    });
});
