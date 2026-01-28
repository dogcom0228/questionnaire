# Phase 4 Complete: Infrastructure Layer - Event Sourcing

## Summary

Phase 4 has been completed. The event sourcing infrastructure is now fully in place using Spatie Laravel Event Sourcing v7.

## What Was Implemented

### 4.1 Event Store ✅
- **Decision**: Using Spatie's default `EloquentStoredEventRepository`
- **Rationale**: Standard MySQL/JSON storage is sufficient; no custom requirements
- **Configuration**: `config/event-sourcing.php` with all default settings
- **Documentation**: `docs/event-sourcing.md`

### 4.2 Snapshot Store ✅
- **Decision**: Using Spatie's default `EloquentSnapshotRepository`
- **Configuration**: Snapshot interval set to 100 events
- **Migration**: Created `snapshots` table schema

### 4.3 Event Store Migrations ✅
Created two migration files:
- `2024_01_01_000005_create_stored_events_table.php`
  - Stores all domain events with JSON serialization
  - Indexed on `aggregate_uuid` and `event_class`
  - Unique constraint on `(aggregate_uuid, aggregate_version)`
  
- `2024_01_01_000006_create_snapshots_table.php`
  - Stores aggregate snapshots for performance
  - Unique constraint on `(aggregate_uuid, aggregate_version)`

### 4.4 Event-Sourced Repositories ✅
Created repository interfaces and implementations:

**Interfaces:**
- `EventSourcedQuestionnaireRepositoryInterface`
- `EventSourcedResponseRepositoryInterface`

**Implementations:**
- `EventSourcedQuestionnaireRepository`
- `EventSourcedResponseRepository`

Both use Spatie's `retrieve()` and `persist()` methods from `AggregateRoot`.

### 4.5 Data Migration Command ✅
Created `MigrateToEventSourcingCommand`:
- Migrates existing Eloquent questionnaire data to event sourcing
- Supports `--dry-run` flag for testing
- Supports `--limit` flag for partial migration
- Progress bar and error handling
- Converts legacy data to domain aggregates with proper events

## Architecture Decisions

### Why Spatie Defaults?
1. **Simplicity**: No custom infrastructure code to maintain
2. **Proven**: Used by thousands of Laravel applications
3. **Extensible**: Easy to customize later if requirements change
4. **Performance**: JSON serialization is fast enough for our use case
5. **Standard**: Works out-of-the-box with MySQL/PostgreSQL

### When to Customize?
Consider custom implementations only if:
- Need encryption for sensitive events
- Require alternative storage (NoSQL, event streaming)
- Need custom event versioning/upcasting
- Implementing event store sharding/partitioning

## Integration Points

### Domain Layer
- ✅ Events extend `ShouldBeStored` via `DomainEvent`
- ✅ Aggregates extend Spatie's `AggregateRoot`

### Application Layer
- ⏳ Projectors configured in `config/event-sourcing.php` (stubbed)
- ⏳ Command handlers will use event-sourced repositories

### Infrastructure Layer
- ✅ Event store migrations created
- ✅ Repository implementations ready
- ✅ Migration command for legacy data

## Next Steps (Phase 5)

1. **5.1-5.3**: Refactor HTTP layer (Controllers, Requests, Resources)
2. **5.4**: Create Read Models (projections from events)
3. **5.5**: Update routing for CQRS separation
4. **5.6**: Wire everything in ServiceProvider
5. **5.7**: Create console commands (RebuildProjections, CreateSnapshot, ReplayEvents)

## Testing the Infrastructure

To test the event sourcing setup:

```bash
# Run migrations (in consuming Laravel app)
php artisan migrate

# Test migration command (dry-run)
php artisan questionnaire:migrate-to-event-sourcing --dry-run --limit=10

# Actually migrate data
php artisan questionnaire:migrate-to-event-sourcing
```

## Files Created

```
config/
  └── event-sourcing.php
database/migrations/
  ├── 2024_01_01_000005_create_stored_events_table.php
  └── 2024_01_01_000006_create_snapshots_table.php
src/
  ├── Contracts/
  │   ├── EventSourcedQuestionnaireRepositoryInterface.php
  │   └── EventSourcedResponseRepositoryInterface.php
  └── Infrastructure/
      ├── Console/Commands/
      │   └── MigrateToEventSourcingCommand.php
      └── Persistence/EventSourcedRepositories/
          ├── EventSourcedQuestionnaireRepository.php
          └── EventSourcedResponseRepository.php
docs/
  └── event-sourcing.md
```

---

**Status**: Phase 4 Complete ✅  
**Next**: Phase 5 - HTTP Layer & Service Provider
