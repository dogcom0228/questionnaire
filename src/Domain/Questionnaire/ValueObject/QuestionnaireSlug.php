<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\ValueObject;

use Illuminate\Support\Str;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Exception\InvalidQuestionnaireSlugException;
use Liangjin0228\Questionnaire\Domain\Shared\ValueObject\ValueObject;

final class QuestionnaireSlug extends ValueObject
{
    private const MIN_LENGTH = 3;

    private const MAX_LENGTH = 255;

    private const PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    private function __construct(
        private readonly string $slug
    ) {
        $this->validate();
    }

    public static function fromString(string $slug): self
    {
        return new self($slug);
    }

    public static function fromTitle(QuestionnaireTitle $title): self
    {
        return new self(Str::slug($title->value()));
    }

    private function validate(): void
    {
        $length = strlen($this->slug);

        if ($length < self::MIN_LENGTH) {
            throw InvalidQuestionnaireSlugException::tooShort(self::MIN_LENGTH);
        }

        if ($length > self::MAX_LENGTH) {
            throw InvalidQuestionnaireSlugException::tooLong(self::MAX_LENGTH);
        }

        if (! preg_match(self::PATTERN, $this->slug)) {
            throw InvalidQuestionnaireSlugException::invalidFormat();
        }
    }

    public function equals(ValueObject $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        return $this->slug === $other->slug;
    }

    public function value(): string
    {
        return $this->slug;
    }
}
