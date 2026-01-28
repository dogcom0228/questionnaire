<?php

declare(strict_types=1);

return [
    /*
     * Aggregate roots are entities that record events. Typically, you'll
     * retrieve the aggregate from a repository, execute a command that
     * records one or more events, and then persist the updated aggregate.
     */
    'aggregate_roots' => [
        //
    ],

    /*
     * Projectors are classes that build up projections from events. You can
     * think of projections as read models. They are stored in the database.
     */
    'projectors' => [
        // Liangjin0228\Questionnaire\Application\Projector\QuestionnaireProjector::class,
        // Liangjin0228\Questionnaire\Application\Projector\ResponseProjector::class,
    ],

    /*
     * Reactors are like projectors but are not responsible for maintaining
     * state. They react to events without storing anything. For example,
     * sending emails after an event occurred.
     */
    'reactors' => [
        //
    ],

    /*
     * This class is responsible for storing events in the EloquentStoredEventRepository.
     * To add extra behaviour you can change this to a class of your own. It should
     * extend the \Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent model.
     */
    'stored_event_model' => Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent::class,

    /*
     * This class is responsible for storing events. To add extra behaviour you
     * can change this to a class of your own. The only restriction is that
     * it should implement \Spatie\EventSourcing\StoredEvents\Repositories\StoredEventRepository.
     */
    'stored_event_repository' => Spatie\EventSourcing\StoredEvents\Repositories\EloquentStoredEventRepository::class,

    /*
     * This class is responsible for storing snapshots. To add extra behaviour you
     * can change this to a class of your own. It should implement
     * \Spatie\EventSourcing\Snapshots\SnapshotRepository.
     */
    'snapshot_repository' => Spatie\EventSourcing\Snapshots\EloquentSnapshotRepository::class,

    /*
     * This class is responsible for handling stored events. To add extra behaviour you
     * can change this to a class of your own. It should extend
     * \Spatie\EventSourcing\StoredEvents\HandleDomainEventJob.
     */
    'stored_event_job' => Spatie\EventSourcing\StoredEvents\HandleStoredEventJob::class,

    /*
     * This class is responsible for serializing events. By default we use the
     * JsonEventSerializer which serializes events to JSON using PHP's built in
     * json_encode function.
     */
    'event_serializer' => Spatie\EventSourcing\EventSerializers\JsonEventSerializer::class,

    /*
     * When replaying events, potentially a lot of events will be retrieved.
     * In order to not run out of memory, events are fetched in chunks.
     */
    'replay_chunk_size' => 1000,

    /*
     * When using snapshots, this value determines the interval at which
     * snapshots should be stored. A value of 20 means that a snapshot
     * will be stored every 20 events.
     */
    'snapshot_interval' => 100,

    /*
     * Similar to Relation::morphMap() you can define which alias responds to which
     * event class. This allows you to change the namespace or class names
     * of your events but still handle older events correctly.
     */
    'event_class_map' => [
        // 'questionnaire_created' => Liangjin0228\Questionnaire\Domain\Questionnaire\Event\QuestionnaireCreated::class,
    ],

    /*
     * This class is responsible for dispatching events to projectors and reactors.
     */
    'dispatcher' => Spatie\EventSourcing\EventHandlers\EventDispatcher::class,

    /*
     * These classes normalize and restore your events when they're serialized. They allow
     * you to efficiently store PHP objects like Carbon instances, Eloquent models, and
     * Collections. If you need to store other complex data, you can add your own normalizers
     * to the chain.
     */
    'event_normalizers' => [
        Spatie\EventSourcing\Support\CarbonNormalizer::class,
        Spatie\EventSourcing\Support\ModelIdentifierNormalizer::class,
        Symfony\Component\Serializer\Normalizer\DateTimeNormalizer::class,
        Symfony\Component\Serializer\Normalizer\ArrayDenormalizer::class,
        Spatie\EventSourcing\Support\ObjectNormalizer::class,
    ],

    /*
     * When a command handler hits an exception, we'll handle it using this class.
     * By default, we'll throw the exception again, but you can override this behavior.
     */
    'catch_exceptions' => false,

    /*
     * This is the queue that will be used for all jobs. If you want to use
     * a specific queue for a specific job, you can override this in the job itself.
     */
    'queue' => env('EVENT_SOURCING_QUEUE'),
];
