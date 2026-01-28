<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Response\Specification;

use Liangjin0228\Questionnaire\Domain\Response\Aggregate\Response;
use Liangjin0228\Questionnaire\Domain\Shared\Specification\Specification;

/**
 * @extends Specification<Response>
 */
final class ResponseIsAuthenticatedSpecification extends Specification
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (! $candidate instanceof Response) {
            return false;
        }

        return $candidate->isAuthenticated();
    }
}
