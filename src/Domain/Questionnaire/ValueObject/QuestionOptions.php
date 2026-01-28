<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject;

use Liangjin0228\Questionnaire\Domain\Shared\ValueObject\ValueObject;

final class QuestionOptions extends ValueObject
{
    /**
     * @param  array<int, string>  $options
     */
    private function __construct(
        private readonly array $options
    ) {}

    /**
     * @param  array<int, string>  $options
     */
    public static function fromArray(array $options): self
    {
        // Normalize array to sequential numeric keys
        $normalizedOptions = array_values(array_filter(
            array_map('trim', $options),
            fn (string $option) => $option !== ''
        ));

        return new self($normalizedOptions);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return array<int, string>
     */
    public function value(): array
    {
        return $this->options;
    }

    public function isEmpty(): bool
    {
        return empty($this->options);
    }

    public function count(): int
    {
        return count($this->options);
    }

    public function hasOption(string $option): bool
    {
        return in_array(trim($option), $this->options, true);
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->value() === $other->value();
    }

    public function __toString(): string
    {
        return implode(', ', $this->options);
    }

    /**
     * @return array<int, string>
     */
    public function jsonSerialize(): array
    {
        return $this->value();
    }
}
