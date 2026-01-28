<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Liangjin0228\Questionnaire\Contracts\EventSourcedQuestionnaireRepositoryInterface;
use Liangjin0228\Questionnaire\Contracts\EventSourcedResponseRepositoryInterface;
use Liangjin0228\Questionnaire\Infrastructure\ReadModel\QuestionnaireModel;
use Liangjin0228\Questionnaire\Infrastructure\ReadModel\ResponseModel;

/**
 * Create snapshots for aggregates to improve performance.
 *
 * Snapshots store the current state of aggregates, reducing the need
 * to replay all events when loading an aggregate.
 *
 * @example php artisan questionnaire:create-snapshot
 * @example php artisan questionnaire:create-snapshot --type=questionnaire --uuid=550e8400-e29b-41d4-a716-446655440000
 */
final class CreateSnapshotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'questionnaire:create-snapshot
                            {--type= : Aggregate type (questionnaire or response)}
                            {--uuid= : Specific aggregate UUID to snapshot}
                            {--all : Create snapshots for all aggregates}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create snapshots for aggregates to improve performance';

    /**
     * Execute the console command.
     */
    public function handle(
        EventSourcedQuestionnaireRepositoryInterface $questionnaireRepo,
        EventSourcedResponseRepositoryInterface $responseRepo
    ): int {
        $type = $this->option('type');
        $uuid = $this->option('uuid');
        $all = $this->option('all');

        if (! $type && ! $all) {
            $this->error('Please specify --type or --all');

            return self::FAILURE;
        }

        if ($all) {
            $this->info('Creating snapshots for all aggregates...');
            $this->createAllSnapshots($questionnaireRepo, $responseRepo);

            return self::SUCCESS;
        }

        if (! in_array($type, ['questionnaire', 'response'])) {
            $this->error('Invalid type. Must be "questionnaire" or "response"');

            return self::FAILURE;
        }

        if ($uuid) {
            $this->createSingleSnapshot($type, $uuid, $questionnaireRepo, $responseRepo);
        } else {
            $this->createSnapshotsForType($type, $questionnaireRepo, $responseRepo);
        }

        return self::SUCCESS;
    }

    private function createAllSnapshots(
        EventSourcedQuestionnaireRepositoryInterface $questionnaireRepo,
        EventSourcedResponseRepositoryInterface $responseRepo
    ): void {
        $this->createSnapshotsForType('questionnaire', $questionnaireRepo, $responseRepo);
        $this->createSnapshotsForType('response', $questionnaireRepo, $responseRepo);

        $this->newLine();
        $this->info('✓ All snapshots created successfully!');
    }

    private function createSnapshotsForType(
        string $type,
        EventSourcedQuestionnaireRepositoryInterface $questionnaireRepo,
        EventSourcedResponseRepositoryInterface $responseRepo
    ): void {
        $this->info("Creating snapshots for all {$type} aggregates...");

        if ($type === 'questionnaire') {
            $models = QuestionnaireModel::all();
            $bar = $this->output->createProgressBar($models->count());

            foreach ($models as $model) {
                if ($model->uuid) {
                    $aggregate = $questionnaireRepo->retrieve($model->uuid);
                    $aggregate->snapshot();
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("✓ Created snapshots for {$models->count()} questionnaire(s)");
        } else {
            $models = ResponseModel::all();
            $bar = $this->output->createProgressBar($models->count());

            foreach ($models as $model) {
                if ($model->uuid) {
                    $aggregate = $responseRepo->retrieve($model->uuid);
                    $aggregate->snapshot();
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("✓ Created snapshots for {$models->count()} response(s)");
        }
    }

    private function createSingleSnapshot(
        string $type,
        string $uuid,
        EventSourcedQuestionnaireRepositoryInterface $questionnaireRepo,
        EventSourcedResponseRepositoryInterface $responseRepo
    ): void {
        $this->info("Creating snapshot for {$type} {$uuid}...");

        try {
            if ($type === 'questionnaire') {
                $aggregate = $questionnaireRepo->retrieve($uuid);
            } else {
                $aggregate = $responseRepo->retrieve($uuid);
            }

            $aggregate->snapshot();

            $this->info("✓ Snapshot created successfully for {$uuid}");
        } catch (\Exception $e) {
            $this->error("Failed to create snapshot: {$e->getMessage()}");
        }
    }
}
