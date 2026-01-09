<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Managers;

use Illuminate\Support\Manager;
use Liangjin0228\Questionnaire\Contracts\QuestionTypeInterface;
use Liangjin0228\Questionnaire\Contracts\QuestionTypeRegistryInterface;
use Liangjin0228\Questionnaire\QuestionTypes\CheckboxQuestionType;
use Liangjin0228\Questionnaire\QuestionTypes\DateQuestionType;
use Liangjin0228\Questionnaire\QuestionTypes\NumberQuestionType;
use Liangjin0228\Questionnaire\QuestionTypes\RadioQuestionType;
use Liangjin0228\Questionnaire\QuestionTypes\SelectQuestionType;
use Liangjin0228\Questionnaire\QuestionTypes\TextareaQuestionType;
use Liangjin0228\Questionnaire\QuestionTypes\TextQuestionType;

class QuestionTypeManager extends Manager implements QuestionTypeRegistryInterface
{
    /**
     * The registered question type identifiers.
     *
     * @var array<int, string>
     */
    protected array $registeredIdentifiers = [
        'text',
        'textarea',
        'number',
        'date',
        'radio',
        'checkbox',
        'select',
    ];

    public function getDefaultDriver(): string
    {
        return 'text';
    }

    /**
     * Register a new question type.
     * Note: In the Manager pattern, we usually use 'extend', but this adapter supports the interface.
     */
    public function register(string $questionTypeClass): void
    {
        // For backward compatibility with the interface which expects a class name.
        // We resolve an instance to get the identifier.
        $instance = app($questionTypeClass);

        if (! $instance instanceof QuestionTypeInterface) {
            throw new \InvalidArgumentException("Class {$questionTypeClass} must implement QuestionTypeInterface.");
        }

        $id = $instance->getIdentifier();

        if (! in_array($id, $this->registeredIdentifiers)) {
            $this->registeredIdentifiers[] = $id;
        }

        // Register as a custom driver
        $this->extend($id, fn () => $instance);
    }

    public function get(string $identifier): ?QuestionTypeInterface
    {
        try {
            return $this->driver($identifier);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    public function getOrFail(string $identifier): QuestionTypeInterface
    {
        return $this->driver($identifier);
    }

    public function has(string $identifier): bool
    {
        return in_array($identifier, $this->registeredIdentifiers) || $this->hasCustomDriver($identifier);
    }

    public function all(): array
    {
        $types = [];
        // Combine default registered identifiers with any custom extensions
        $allIds = array_unique(array_merge($this->registeredIdentifiers, array_keys($this->customCreators)));

        foreach ($allIds as $id) {
            try {
                $types[$id] = $this->driver($id);
            } catch (\Throwable $e) {
                // Skip invalid drivers
                continue;
            }
        }

        return $types;
    }

    public function toArray(): array
    {
        return array_values(
            array_map(fn (QuestionTypeInterface $type) => $type->toArray(), $this->all())
        );
    }

    public function unregister(string $identifier): void
    {
        $this->registeredIdentifiers = array_filter(
            $this->registeredIdentifiers,
            fn ($id) => $id !== $identifier
        );

        // Remove from resolved instances
        unset($this->drivers[$identifier]);
        // Remove from custom creators
        unset($this->customCreators[$identifier]);
    }

    protected function hasCustomDriver($driver)
    {
        return isset($this->customCreators[$driver]);
    }

    // -- Drivers --

    public function createTextDriver(): QuestionTypeInterface
    {
        return new TextQuestionType;
    }

    public function createTextareaDriver(): QuestionTypeInterface
    {
        return new TextareaQuestionType;
    }

    public function createNumberDriver(): QuestionTypeInterface
    {
        return new NumberQuestionType;
    }

    public function createDateDriver(): QuestionTypeInterface
    {
        return new DateQuestionType;
    }

    public function createRadioDriver(): QuestionTypeInterface
    {
        return new RadioQuestionType;
    }

    public function createCheckboxDriver(): QuestionTypeInterface
    {
        return new CheckboxQuestionType;
    }

    public function createSelectDriver(): QuestionTypeInterface
    {
        return new SelectQuestionType;
    }
}
