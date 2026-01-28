<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Response\Guard;

use Liangjin0228\Questionnaire\Contracts\DuplicateSubmissionGuardInterface;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;

/**
 * Factory for resolving duplicate submission guards based on questionnaire settings.
 */
class DuplicateSubmissionGuardFactory
{
    /**
     * @var array<string, class-string<DuplicateSubmissionGuardInterface>>
     */
    protected array $guards = [];

    public function __construct()
    {
        $this->registerDefaultGuards();
    }

    /**
     * Register a guard.
     *
     * @param  class-string<DuplicateSubmissionGuardInterface>  $guardClass
     */
    public function register(string $identifier, string $guardClass): void
    {
        $this->guards[$identifier] = $guardClass;
    }

    /**
     * Resolve a guard for a questionnaire.
     */
    public function resolve(Questionnaire $questionnaire): DuplicateSubmissionGuardInterface
    {
        $strategy = $questionnaire->duplicate_submission_strategy ?? 'allow_multiple';

        if (! isset($this->guards[$strategy])) {
            $strategy = 'allow_multiple';
        }

        return app($this->guards[$strategy]);
    }

    /**
     * Get all registered guards.
     *
     * @return array<string, class-string<DuplicateSubmissionGuardInterface>>
     */
    public function getRegisteredGuards(): array
    {
        return $this->guards;
    }

    /**
     * Register default guards.
     */
    protected function registerDefaultGuards(): void
    {
        $this->guards = [
            'allow_multiple' => AllowMultipleGuard::class,
            'one_per_user' => OnePerUserGuard::class,
            'one_per_session' => OnePerSessionGuard::class,
            'one_per_ip' => OnePerIpGuard::class,
        ];
    }
}
