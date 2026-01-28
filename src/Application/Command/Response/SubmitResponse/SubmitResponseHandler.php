<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\Command\Response\SubmitResponse;

use Liangjin0228\Questionnaire\Contracts\Application\Bus\EventBusInterface;
use Liangjin0228\Questionnaire\Contracts\Application\CommandHandlerInterface;
use Liangjin0228\Questionnaire\Contracts\Application\CommandInterface;
use Liangjin0228\Questionnaire\Contracts\QuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Contracts\ResponseRepositoryInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Response\Aggregate\Response;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\IpAddress;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\Respondent;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\ResponseId;
use Liangjin0228\Questionnaire\Domain\Response\ValueObject\UserAgent;

final readonly class SubmitResponseHandler implements CommandHandlerInterface
{
    public function __construct(
        private QuestionnaireRepositoryInterface $questionnaireRepository,
        private ResponseRepositoryInterface $responseRepository,
        private EventBusInterface $eventBus
    ) {}

    public function handle(CommandInterface $command): string
    {
        assert($command instanceof SubmitResponseCommand);

        $questionnaireId = QuestionnaireId::fromString($command->questionnaireId);
        $responseId = ResponseId::generate();

        $respondent = $command->userId
            ? Respondent::authenticated('user', $command->userId)
            : Respondent::anonymous();

        $ipAddress = $command->ipAddress ? IpAddress::fromString($command->ipAddress) : null;
        $userAgent = UserAgent::fromString('');

        $response = Response::submit(
            id: $responseId,
            questionnaireId: $questionnaireId,
            respondent: $respondent,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            answers: []
        );

        return (string) $responseId;
    }
}
