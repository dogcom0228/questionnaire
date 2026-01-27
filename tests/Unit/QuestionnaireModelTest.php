<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Tests\Unit;

use Liangjin0228\Questionnaire\Domain\Questionnaire\Enums\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Tests\TestCase;

class QuestionnaireModelTest extends TestCase
{
    public function test_get_statuses_returns_array(): void
    {
        $statuses = Questionnaire::getStatuses();

        $this->assertIsArray($statuses);
        $this->assertArrayHasKey('draft', $statuses);
        $this->assertArrayHasKey('published', $statuses);
        $this->assertArrayHasKey('closed', $statuses);
    }

    public function test_is_active_attribute_exists(): void
    {
        $questionnaire = new Questionnaire([
            'title' => 'Test',
            'status' => QuestionnaireStatus::PUBLISHED->value,
        ]);

        $this->assertArrayHasKey('is_active', $questionnaire->toArray());
    }

    public function test_is_accepting_responses_attribute_exists(): void
    {
        $questionnaire = new Questionnaire([
            'title' => 'Test',
            'status' => QuestionnaireStatus::PUBLISHED->value,
        ]);

        $this->assertArrayHasKey('is_accepting_responses', $questionnaire->toArray());
    }

    public function test_fillable_attributes(): void
    {
        $fillable = (new Questionnaire)->getFillable();

        $expectedAttributes = [
            'title',
            'description',
            'slug',
            'status',
            'settings',
            'starts_at',
            'ends_at',
            'published_at',
            'closed_at',
            'user_id',
            'requires_auth',
            'submission_limit',
            'duplicate_submission_strategy',
        ];

        foreach ($expectedAttributes as $attribute) {
            $this->assertContains($attribute, $fillable);
        }
    }

    public function test_casts_attributes_correctly(): void
    {
        $questionnaire = new Questionnaire;
        $casts = $questionnaire->getCasts();

        $this->assertEquals('array', $casts['settings']);
        $this->assertEquals('datetime', $casts['starts_at']);
        $this->assertEquals('datetime', $casts['ends_at']);
        $this->assertEquals('datetime', $casts['published_at']);
        $this->assertEquals('datetime', $casts['closed_at']);
        $this->assertEquals('boolean', $casts['requires_auth']);
        $this->assertEquals('integer', $casts['submission_limit']);
    }
}
