# Event Sourcing Implementation

## Overview

This package uses **Spatie Laravel Event Sourcing** (v7) for event store infrastructure.

## Architecture Decision

We use **Spatie's default implementations** for:
- Event Store: `EloquentStoredEventRepository`
- Event Serializer: `JsonEventSerializer`
- Snapshot Store: `EloquentSnapshotRepository`

### Why Defaults?

Based on package requirements analysis:
- Standard MySQL/PostgreSQL storage is sufficient
- JSON serialization works for our value objects
- No special encryption/security requirements needed
- No alternative storage backends required
- Covers 90% of use cases per Spatie documentation

### When to Customize

Consider custom implementations only if you need:
- Alternative storage (NoSQL, event streaming)
- Event encryption for sensitive data
- Custom event versioning/upcasting logic
- Event store partitioning/sharding

## Database Schema

### stored_events Table
```sql
- id: auto-incrementing (global event ordering)
- aggregate_uuid: links events to aggregates
- aggregate_version: version within aggregate
- event_version: schema version (for upgrades)
- event_class: FQCN of event
- event_properties: JSON event payload
- meta_data: Additional context
- created_at: timestamp
- UNIQUE(aggregate_uuid, aggregate_version)
```

### snapshots Table
```sql
- id: auto-incrementing
- aggregate_uuid: links to aggregate
- aggregate_version: snapshot version
- state: JSON serialized aggregate state
- created_at: timestamp
- UNIQUE(aggregate_uuid, aggregate_version)
```

## Configuration

See `config/event-sourcing.php` for:
- Projector/Reactor registration
- Snapshot interval (default: 100 events)
- Event normalizers
- Queue configuration

## Domain Integration

Domain events already extend `Spatie\EventSourcing\StoredEvents\ShouldBeStored` via `Domain\Shared\Event\DomainEvent`.

Aggregates extend `Spatie\EventSourcing\AggregateRoots\AggregateRoot` via `Domain\Shared\Aggregate\AggregateRoot`.

No code changes needed - infrastructure is plug-and-play.
