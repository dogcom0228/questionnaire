<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Spatie\EventSourcing\Facades\Projectionist;

/**
 * Rebuild all projections from stored events.
 *
 * This command replays all stored events to rebuild the read models (projections).
 * Useful after:
 * - Adding new projectors
 * - Fixing bugs in existing projectors
 * - Data corruption in read models
 *
 * @example php artisan questionnaire:rebuild-projections
 * @example php artisan questionnaire:rebuild-projections --from=2024-01-01
 */
final class RebuildProjectionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'questionnaire:rebuild-projections
                            {--from= : Starting event ID to replay from}
                            {--projector=* : Specific projector class names to rebuild}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild all projections from stored events';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting projection rebuild...');

        $projectors = $this->option('projector');
        $from = $this->option('from');

        if (empty($projectors)) {
            // Rebuild all projectors
            $this->info('Rebuilding all projectors...');

            if ($from) {
                Projectionist::replay(
                    fn ($query) => $query->where('id', '>=', $from)
                );
            } else {
                Projectionist::replay();
            }

            $this->info('✓ All projections rebuilt successfully.');
        } else {
            // Rebuild specific projectors
            foreach ($projectors as $projectorClass) {
                if (! class_exists($projectorClass)) {
                    $this->error("Projector class not found: {$projectorClass}");

                    continue;
                }

                $this->info("Rebuilding projector: {$projectorClass}");

                if ($from) {
                    Projectionist::replay(
                        fn ($query) => $query->where('id', '>=', $from),
                        [$projectorClass]
                    );
                } else {
                    Projectionist::replay(null, [$projectorClass]);
                }

                $this->info("✓ {$projectorClass} rebuilt successfully.");
            }
        }

        $this->newLine();
        $this->info('Projection rebuild completed!');

        return self::SUCCESS;
    }
}
