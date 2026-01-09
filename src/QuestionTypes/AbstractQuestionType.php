<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\QuestionTypes;

use Liangjin0228\Questionnaire\Contracts\QuestionTypeInterface;
use Liangjin0228\Questionnaire\Models\Question;

abstract class AbstractQuestionType implements QuestionTypeInterface
{
    /**
     * {@inheritdoc}
     */
    abstract public function getIdentifier(): string;

    /**
     * {@inheritdoc}
     */
    abstract public function getName(): string;

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon(): string
    {
        return 'mdi-help-circle';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsOptions(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationRules(Question $question): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationMessages(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function transformValue(mixed $value): mixed
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function formatValue(mixed $value, Question $question): string
    {
        if (is_array($value)) {
            return implode(', ', $value);
        }

        return (string) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getVueComponent(): string
    {
        return 'QuestionType' . ucfirst($this->getIdentifier());
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'identifier' => $this->getIdentifier(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'icon' => $this->getIcon(),
            'supports_options' => $this->supportsOptions(),
            'vue_component' => $this->getVueComponent(),
            'config' => $this->getConfig(),
        ];
    }
}
