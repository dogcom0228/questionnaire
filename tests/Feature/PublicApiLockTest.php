<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Tests\Feature;

use Liangjin0228\Questionnaire\Enums\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Tests\TestCase;

class PublicApiLockTest extends TestCase
{
    /**
     * @test
     */
    public function test_it_denies_access_when_questionnaire_is_not_published(): void
    {
        $questionnaire = Questionnaire::factory()->create([
            'status' => QuestionnaireStatus::DRAFT->value,
        ]);

        $response = $this->getJson(route('questionnaire.api.public', $questionnaire));

        $response->assertStatus(403);
        $response->assertJson(['message' => 'This questionnaire is not accepting responses.']);
    }

    /**
     * @test
     */
    public function test_it_denies_access_when_submission_limit_is_reached(): void
    {
        $questionnaire = Questionnaire::factory()->create([
            'status' => QuestionnaireStatus::PUBLISHED->value,
            'submission_limit' => 2,
        ]);

        // Create 2 responses
        $questionnaire->responses()->create(['data' => []]);
        $questionnaire->responses()->create(['data' => []]);

        $response = $this->getJson(route('questionnaire.api.public', $questionnaire));

        $response->assertStatus(403);
        $response->assertJson(['message' => 'This questionnaire is not accepting responses.']);
    }

    /**
     * @test
     */
    public function test_it_denies_submission_when_limit_is_reached(): void
    {
        $questionnaire = Questionnaire::factory()->create([
            'status' => QuestionnaireStatus::PUBLISHED->value,
            'submission_limit' => 2,
        ]);

        // Create 2 responses
        $questionnaire->responses()->create(['data' => []]);
        $questionnaire->responses()->create(['data' => []]);

        $response = $this->postJson(route('questionnaire.api.submit', $questionnaire), [
            'answers' => [],
        ]);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'This questionnaire is not accepting responses.']);
    }

    /**
     * @test
     */
    public function test_it_allows_access_when_limit_is_not_reached(): void
    {
        $questionnaire = Questionnaire::factory()->create([
            'status' => QuestionnaireStatus::PUBLISHED->value,
            'submission_limit' => 2,
        ]);

        // Create 1 response
        $questionnaire->responses()->create(['data' => []]);

        $response = $this->getJson(route('questionnaire.api.public', $questionnaire));

        $response->assertStatus(200);
    }
}
