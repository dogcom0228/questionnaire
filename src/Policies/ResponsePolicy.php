<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Liangjin0228\Questionnaire\Models\Response;

class ResponsePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the response.
     */
    public function view($user, Response $response): bool
    {
        // Owner of the questionnaire can view
        if ($response->questionnaire->user_id === $user->getKey()) {
            return true;
        }

        // Respondent can view their own response
        if ($response->respondent_id === $user->getKey() 
            && $response->respondent_type === get_class($user)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the response.
     */
    public function delete($user, Response $response): bool
    {
        // Only questionnaire owner can delete responses
        return $response->questionnaire->user_id === $user->getKey();
    }
}
