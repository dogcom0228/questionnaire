<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Tests\Stubs;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function version(Request $request): ?string
    {
        return 'testing';
    }

    public function share(Request $request): array
    {
        return [];
    }
}
