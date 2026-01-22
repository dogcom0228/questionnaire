<?php

namespace Liangjin0228\Questionnaire\Tests\Feature;

use Liangjin0228\Questionnaire\Models\Questionnaire;
use Liangjin0228\Questionnaire\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ZeroConfigTest extends TestCase
{
    #[Test]
    public function it_can_render_the_embed_view()
    {
        $questionnaire = Questionnaire::factory()->create();

        $response = $this->get("/questionnaire/embed/{$questionnaire->id}");

        $response->assertStatus(200);
        $response->assertSee('<div id="questionnaire-app"', false);
    }
}
