<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Console;

use Illuminate\Console\Command;
use Liangjin0228\Questionnaire\Contracts\QuestionTypeRegistryInterface;

class ListQuestionTypesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'questionnaire:question-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all registered question types';

    /**
     * Execute the console command.
     */
    public function handle(QuestionTypeRegistryInterface $registry): int
    {
        $types = $registry->all();

        if (empty($types)) {
            $this->warn('No question types registered.');

            return Command::SUCCESS;
        }

        $rows = [];
        foreach ($types as $identifier => $type) {
            $rows[] = [
                $identifier,
                $type->getName(),
                $type->getDescription(),
                $type->supportsOptions() ? 'Yes' : 'No',
                $type->getIcon(),
            ];
        }

        $this->table(
            ['Identifier', 'Name', 'Description', 'Has Options', 'Icon'],
            $rows
        );

        return Command::SUCCESS;
    }
}
