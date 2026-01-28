<?php

declare(strict_types=1);

arch('Domain layer does not depend on Infrastructure')
    ->expect('Liangjin0228\Questionnaire\Domain')
    ->not->toUse('Liangjin0228\Questionnaire\Infrastructure');

arch('Domain layer does not depend on Application')
    ->expect('Liangjin0228\Questionnaire\Domain')
    ->not->toUse('Liangjin0228\Questionnaire\Application');

arch('Domain layer does not depend on Laravel framework')
    ->expect('Liangjin0228\Questionnaire\Domain')
    ->not->toUse([
        'Illuminate\Http',
        'Illuminate\Routing',
        'Illuminate\Console',
    ]);

arch('Application layer does not depend on Infrastructure HTTP')
    ->expect('Liangjin0228\Questionnaire\Application')
    ->not->toUse('Liangjin0228\Questionnaire\Infrastructure\Http');

arch('Application layer does not depend on Infrastructure Console')
    ->expect('Liangjin0228\Questionnaire\Application')
    ->not->toUse('Liangjin0228\Questionnaire\Infrastructure\Console');

arch('Value Objects are immutable and final')
    ->expect('Liangjin0228\Questionnaire\Domain')
    ->classes()
    ->that->haveNameMatching('/.*ValueObject$/')
    ->toBeFinal()
    ->toBeReadonly();

arch('Domain Events extend DomainEvent base class')
    ->expect('Liangjin0228\Questionnaire\Domain')
    ->classes()
    ->that->haveNameMatching('/.*Event(s)?\/.*/')
    ->toExtend('Liangjin0228\Questionnaire\Domain\Shared\Event\DomainEvent');

arch('Aggregates extend AggregateRoot')
    ->expect('Liangjin0228\Questionnaire\Domain')
    ->classes()
    ->that->haveNameMatching('/.*(Aggregate|AggregateRoot)$/')
    ->toExtend('Liangjin0228\Questionnaire\Domain\Shared\Aggregate\AggregateRoot');

arch('Command Handlers are final and readonly')
    ->expect('Liangjin0228\Questionnaire\Application\CommandHandler')
    ->classes()
    ->toBeFinal()
    ->toBeReadonly();

arch('Query Handlers are final and readonly')
    ->expect('Liangjin0228\Questionnaire\Application\QueryHandler')
    ->classes()
    ->toBeFinal()
    ->toBeReadonly();

arch('Commands implement CommandInterface')
    ->expect('Liangjin0228\Questionnaire\Application\Command')
    ->classes()
    ->toImplement('Liangjin0228\Questionnaire\Contracts\Application\CommandInterface');

arch('Queries implement QueryInterface')
    ->expect('Liangjin0228\Questionnaire\Application\Query')
    ->classes()
    ->toImplement('Liangjin0228\Questionnaire\Contracts\Application\QueryInterface');

arch('Controllers are in Infrastructure HTTP layer')
    ->expect('Liangjin0228\Questionnaire\Infrastructure\Http\Controllers')
    ->toOnlyBeUsedIn('Liangjin0228\Questionnaire\Infrastructure\Http\Controllers');

arch('Strict types are declared in all PHP files')
    ->expect('Liangjin0228\Questionnaire')
    ->toUseStrictTypes();

arch('No global functions are used')
    ->expect('Liangjin0228\Questionnaire\Domain')
    ->not->toUse([
        'dd',
        'dump',
        'var_dump',
        'print_r',
        'exit',
        'die',
    ]);

arch('Repositories follow naming convention')
    ->expect('Liangjin0228\Questionnaire')
    ->classes()
    ->that->haveNameMatching('/.*Repository(Interface)?$/')
    ->toHaveSuffix('Repository')
    ->or->toHaveSuffix('RepositoryInterface');

arch('Projectors are final')
    ->expect('Liangjin0228\Questionnaire\Application\Projector')
    ->classes()
    ->toBeFinal();

arch('Read Models are in Infrastructure layer')
    ->expect('Liangjin0228\Questionnaire\Infrastructure\ReadModel')
    ->toOnlyBeUsedIn([
        'Liangjin0228\Questionnaire\Infrastructure',
        'Liangjin0228\Questionnaire\Application\Projector',
        'Liangjin0228\Questionnaire\Application\QueryHandler',
    ]);

arch('Domain Models do not extend Eloquent Model')
    ->expect('Liangjin0228\Questionnaire\Domain')
    ->classes()
    ->not->toExtend('Illuminate\Database\Eloquent\Model');
