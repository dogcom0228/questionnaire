<?php

declare(strict_types=1);

test('list question types command runs successfully', function () {
    $this->markTestSkipped('Command removed during DDD refactoring');

    $this->artisan('questionnaire:question-types')
        ->assertExitCode(0);
});
