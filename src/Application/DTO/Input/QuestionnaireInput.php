<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Application\DTO\Input;

final readonly class QuestionnaireInput
{
    /**
     * @param  array<string, mixed>|null  $settings
     * @param  array<int, QuestionInput>  $questions
     */
    public function __construct(
        public string $title,
        public ?string $description = null,
        public ?array $settings = null,
        public array $questions = []
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $questions = [];
        if (isset($data['questions']) && is_array($data['questions'])) {
            foreach ($data['questions'] as $questionData) {
                $questions[] = QuestionInput::fromArray($questionData);
            }
        }

        return new self(
            title: $data['title'],
            description: $data['description'] ?? null,
            settings: $data['settings'] ?? null,
            questions: $questions
        );
    }
}
