<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Contracts;

interface QuestionTypeRegistryInterface
{
    /**
     * Register a question type.
     *
     * @param  class-string<QuestionTypeInterface>  $questionTypeClass
     */
    public function register(string $questionTypeClass): void;

    /**
     * Get a question type by identifier.
     */
    public function get(string $identifier): ?QuestionTypeInterface;

    /**
     * Get a question type by identifier or throw exception.
     *
     * @throws \InvalidArgumentException
     */
    public function getOrFail(string $identifier): QuestionTypeInterface;

    /**
     * Check if a question type is registered.
     */
    public function has(string $identifier): bool;

    /**
     * Get all registered question types.
     *
     * @return array<string, QuestionTypeInterface>
     */
    public function all(): array;

    /**
     * Get all question types as array (for frontend).
     *
     * @return array<int, array<string, mixed>>
     */
    public function toArray(): array;

    /**
     * Unregister a question type.
     */
    public function unregister(string $identifier): void;
}
