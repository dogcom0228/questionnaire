<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;

/**
 * Replay specific events from the event store.
 *
 * Useful for debugging, testing projectors, or fixing data inconsistencies
 * by replaying specific events or event ranges.
 *
 * @example php artisan questionnaire:replay-events --from=100 --to=200
 * @example php artisan questionnaire:replay-events --event=QuestionnaireCreated
 * @example php artisan questionnaire:replay-events --aggregate-uuid=550e8400-e29b-41d4-a716-446655440000
 */
final class ReplayEventsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'questionnaire:replay-events
                            {--from= : Starting event ID}
                            {--to= : Ending event ID}
                            {--event=* : Specific event class names to replay}
                            {--aggregate-uuid= : Replay events for a specific aggregate UUID}
                            {--projector=* : Specific projector class names to use}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Replay specific events from the event store';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $from = $this->option('from');
        $to = $this->option('to');
        $events = $this->option('event');
        $aggregateUuid = $this->option('aggregate-uuid');
        $projectors = $this->option('projector');

        if (! $from && ! $events && ! $aggregateUuid) {
            $this->error('Please specify at least one filter option (--from, --event, or --aggregate-uuid)');

            return self::FAILURE;
        }

        $this->info('Starting event replay...');

        $query = $this->buildQuery($from, $to, $events, $aggregateUuid);
        $eventCount = $query->count();

        if ($eventCount === 0) {
            $this->warn('No events found matching the criteria.');

            return self::SUCCESS;
        }

        $this->info("Found {$eventCount} event(s) to replay.");

        if ($this->confirm('Do you want to continue?', true)) {
            $this->replayEvents($query, $projectors);
            $this->newLine();
            $this->info('✓ Event replay completed successfully!');

            return self::SUCCESS;
        }

        $this->info('Event replay cancelled.');

        return self::SUCCESS;
    }

    /**
     * Build the query based on options.
     */
    private function buildQuery(
        ?string $from,
        ?string $to,
        array $events,
        ?string $aggregateUuid
    ): \Illuminate\Database\Eloquent\Builder {
        $query = EloquentStoredEvent::query();

        if ($from) {
            $query->where('id', '>=', $from);
        }

        if ($to) {
            $query->where('id', '<=', $to);
        }

        if (! empty($events)) {
            $query->whereIn('event_class', $events);
        }

        if ($aggregateUuid) {
            $query->where('aggregate_uuid', $aggregateUuid);
        }

        return $query->orderBy('id');
    }

    /**
     * Replay the events using Projectionist.
     */
    private function replayEvents(
        \Illuminate\Database\Eloquent\Builder $query,
        array $projectors
    ): void {
        $bar = $this->output->createProgressBar($query->count());
        $bar->start();

        if (empty($projectors)) {
            // Replay with all projectors
            Projectionist::replay(
                fn () => $query,
                null
            );
        } else {
            // Replay with specific projectors
            foreach ($projectors as $projectorClass) {
                if (! class_exists($projectorClass)) {
                    $this->error("Projector class not found: {$projectorClass}");

                    continue;
                }

                Projectionist::replay(
                    fn () => $query,
                    [$projectorClass]
                );
            }
        }

        $bar->finish();
    }
}
