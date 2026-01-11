<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Tests\Feature;

use Liangjin0228\Questionnaire\Tests\TestCase;

class CommandTest extends TestCase
{
    public function test_list_question_types_command(): void
    {
        $this->artisan('questionnaire:question-types')
            ->assertExitCode(0);
    }
}
