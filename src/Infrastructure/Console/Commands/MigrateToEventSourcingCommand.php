<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Liangjin0228\Questionnaire\Contracts\EventSourcedQuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Aggregate\Questionnaire;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\DateRange;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSettings;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireSlug;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionnaireTitle;

final class MigrateToEventSourcingCommand extends Command
{
    protected $signature = 'questionnaire:migrate-to-event-sourcing
                            {--dry-run : Run the migration without persisting events}
                            {--limit= : Limit the number of questionnaires to migrate}';

    protected $description = 'Migrate existing questionnaire data to event sourcing';

    public function __construct(
        private readonly EventSourcedQuestionnaireRepositoryInterface $repository
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting migration to event sourcing...');

        $dryRun = $this->option('dry-run');
        $limit = $this->option('limit');

        if ($dryRun) {
            $this->warn('Running in DRY RUN mode - no events will be persisted');
        }

        $query = DB::table(config('questionnaire.table_names.questionnaires'))
            ->orderBy('id');

        if ($limit) {
            $query->limit((int) $limit);
        }

        $questionnaires = $query->get();
        $total = $questionnaires->count();

        if ($total === 0) {
            $this->info('No questionnaires found to migrate.');

            return self::SUCCESS;
        }

        $this->info("Found {$total} questionnaires to migrate.");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $migrated = 0;
        $errors = 0;

        foreach ($questionnaires as $questionnaireData) {
            try {
                $aggregate = $this->createAggregateFromLegacyData($questionnaireData);

                if (! $dryRun) {
                    $this->repository->persist($aggregate);
                }

                $migrated++;
            } catch (\Throwable $e) {
                $errors++;
                $this->newLine();
                $this->error("Failed to migrate questionnaire ID {$questionnaireData->id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Migration completed:');
        $this->info("  Migrated: {$migrated}");

        if ($errors > 0) {
            $this->error("  Errors: {$errors}");
        }

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function createAggregateFromLegacyData(object $data): Questionnaire
    {
        $id = QuestionnaireId::fromString((string) $data->uuid);
        $title = QuestionnaireTitle::fromString($data->title);
        $slug = QuestionnaireSlug::fromString($data->slug);
        $description = $data->description;

        $settings = json_decode($data->settings ?? '{}', true);
        $questionnaireSettings = QuestionnaireSettings::create(
            $settings['max_responses'] ?? null,
            (bool) ($settings['allow_multiple_responses'] ?? false),
            (bool) ($settings['require_auth'] ?? false),
            (bool) ($settings['show_results'] ?? false),
            (bool) ($settings['randomize_questions'] ?? false)
        );

        $dateRange = DateRange::create(
            $data->starts_at ? \Carbon\CarbonImmutable::parse($data->starts_at) : null,
            $data->ends_at ? \Carbon\CarbonImmutable::parse($data->ends_at) : null
        );

        return Questionnaire::create(
            $id,
            $title,
            $slug,
            $description,
            $dateRange,
            $questionnaireSettings
        );
    }
}
