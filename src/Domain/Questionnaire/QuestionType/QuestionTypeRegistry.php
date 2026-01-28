<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\QuestionType;

use Liangjin0228\Questionnaire\Contracts\QuestionTypeInterface;
use Liangjin0228\Questionnaire\Contracts\QuestionTypeRegistryInterface;

class QuestionTypeRegistry implements QuestionTypeRegistryInterface
{
    /**
     * @var array<string, QuestionTypeInterface>
     */
    protected array $types = [];

    /**
     * {@inheritdoc}
     */
    public function register(string $questionTypeClass): void
    {
        $instance = app($questionTypeClass);

        if (! $instance instanceof QuestionTypeInterface) {
            throw new \InvalidArgumentException(
                "Class {$questionTypeClass} must implement QuestionTypeInterface."
            );
        }

        $this->types[$instance->getIdentifier()] = $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $identifier): ?QuestionTypeInterface
    {
        return $this->types[$identifier] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrFail(string $identifier): QuestionTypeInterface
    {
        $type = $this->get($identifier);

        if ($type === null) {
            throw new \InvalidArgumentException(
                "Question type '{$identifier}' is not registered."
            );
        }

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $identifier): bool
    {
        return isset($this->types[$identifier]);
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->types;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_values(
            array_map(fn (QuestionTypeInterface $type) => $type->toArray(), $this->types)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unregister(string $identifier): void
    {
        unset($this->types[$identifier]);
    }
}
