<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Entity;

use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionId;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionOptions;
use Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject\QuestionText;
use Liangjin0228\Questionnaire\Domain\Shared\Entity\Entity;

final class Question extends Entity
{
    /**
     * @param  array<string, mixed>  $settings
     */
    private function __construct(
        QuestionId $id,
        private QuestionText $text,
        private readonly string $type,
        private QuestionOptions $options,
        private bool $required,
        private int $order,
        private ?string $description = null,
        private array $settings = []
    ) {
        parent::__construct($id);
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    public static function create(
        QuestionId $id,
        QuestionText $text,
        string $type,
        QuestionOptions $options,
        bool $required,
        int $order,
        ?string $description = null,
        array $settings = []
    ): self {
        return new self($id, $text, $type, $options, $required, $order, $description, $settings);
    }

    public function id(): QuestionId
    {
        /** @var QuestionId */
        return $this->id;
    }

    public function text(): QuestionText
    {
        return $this->text;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function options(): QuestionOptions
    {
        return $this->options;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function order(): int
    {
        return $this->order;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    /**
     * @return array<string, mixed>
     */
    public function settings(): array
    {
        return $this->settings;
    }

    public function updateText(QuestionText $newText): void
    {
        $this->text = $newText;
    }

    public function updateOptions(QuestionOptions $newOptions): void
    {
        $this->options = $newOptions;
    }

    public function markAsRequired(): void
    {
        $this->required = true;
    }

    public function markAsOptional(): void
    {
        $this->required = false;
    }

    public function updateOrder(int $newOrder): void
    {
        $this->order = $newOrder;
    }

    public function updateDescription(?string $newDescription): void
    {
        $this->description = $newDescription;
    }

    /**
     * @param  array<string, mixed>  $newSettings
     */
    public function updateSettings(array $newSettings): void
    {
        $this->settings = $newSettings;
    }
}
