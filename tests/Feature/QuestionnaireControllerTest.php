<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Tests\Feature;

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Liangjin0228\Questionnaire\Enums\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Tests\TestCase;

class QuestionnaireControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = new User;
        $this->user->forceFill([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->user->save();

        // Ensure default config enables admin features
        config(['questionnaire.features.admin' => true]);
    }

    public function test_index_displays_questionnaires(): void
    {
        Questionnaire::create([
            'title' => 'Test Q1',
            'status' => 'draft',
            'user_id' => $this->user->getKey(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('questionnaire.admin.index'));

        $response->assertOk();
    }

    public function test_create_displays_form(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('questionnaire.admin.create'));

        $response->assertOk();
    }

    public function test_store_creates_questionnaire(): void
    {
        $data = [
            'title' => 'New Questionnaire',
            'description' => 'Description here',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('questionnaire.admin.store'), $data);

        $response->assertRedirect();

        $this->assertDatabaseHas('questionnaires', [
            'title' => 'New Questionnaire',
            'description' => 'Description here',
            'status' => QuestionnaireStatus::DRAFT->value,
        ]);
    }

    public function test_show_displays_questionnaire(): void
    {
        $questionnaire = Questionnaire::create([
            'title' => 'Show Me',
            'status' => 'draft',
            'user_id' => $this->user->getKey(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('questionnaire.admin.show', $questionnaire));

        $response->assertOk();
    }

    public function test_edit_displays_form(): void
    {
        $questionnaire = Questionnaire::create([
            'title' => 'Edit Me',
            'status' => 'draft',
            'user_id' => $this->user->getKey(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('questionnaire.admin.edit', $questionnaire));

        $response->assertOk();
    }

    public function test_update_updates_questionnaire(): void
    {
        $questionnaire = Questionnaire::create([
            'title' => 'Old Title',
            'status' => 'draft',
            'user_id' => $this->user->getKey(),
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('questionnaire.admin.update', $questionnaire), [
                'title' => 'New Title',
                'description' => 'Updated desc',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('questionnaires', [
            'id' => $questionnaire->id,
            'title' => 'New Title',
        ]);
    }

    public function test_destroy_deletes_questionnaire(): void
    {
        $questionnaire = Questionnaire::create([
            'title' => 'Delete Me',
            'status' => 'draft',
            'user_id' => $this->user->getKey(),
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('questionnaire.admin.destroy', $questionnaire));

        $response->assertRedirect(route('questionnaire.admin.index'));

        // Soft deleted?
        $this->assertSoftDeleted('questionnaires', [
            'id' => $questionnaire->id,
        ]);
    }

    public function test_can_publish_questionnaire(): void
    {
        $questionnaire = Questionnaire::create([
            'title' => 'Publish Me',
            'status' => 'draft',
            'user_id' => $this->user->getKey(),
        ]);

        // Add a question to allow publishing
        $questionnaire->questions()->create([
            'type' => 'text',
            'content' => 'Sample Question',
            'required' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('questionnaire.admin.publish', $questionnaire));

        $response->assertRedirect();

        $questionnaire->refresh();
        $this->assertEquals(QuestionnaireStatus::PUBLISHED->value, $questionnaire->status);
        $this->assertNotNull($questionnaire->published_at);
    }

    public function test_can_close_questionnaire(): void
    {
        $questionnaire = Questionnaire::create([
            'title' => 'Close Me',
            'status' => 'published',
            'published_at' => now(),
            'user_id' => $this->user->getKey(),
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('questionnaire.admin.close', $questionnaire));

        $response->assertRedirect();

        $questionnaire->refresh();
        $this->assertEquals(QuestionnaireStatus::CLOSED->value, $questionnaire->status);
    }

    public function test_responses_displays_list(): void
    {
        $questionnaire = Questionnaire::create([
            'title' => 'Responses Q',
            'status' => 'published',
            'user_id' => $this->user->getKey(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('questionnaire.admin.responses', $questionnaire));

        $response->assertOk();
    }
}
