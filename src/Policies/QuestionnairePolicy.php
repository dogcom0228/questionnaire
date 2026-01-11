<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Liangjin0228\Questionnaire\Enums\QuestionnaireStatus;
use Liangjin0228\Questionnaire\Models\Questionnaire;

class QuestionnairePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any questionnaires.
     */
    public function viewAny($user): bool
    {
        // Default: authenticated users can view questionnaire list
        return true;
    }

    /**
     * Determine whether the user can view the questionnaire.
     */
    public function view($user, Questionnaire $questionnaire): bool
    {
        // Owner can always view
        if ($this->isOwner($user, $questionnaire)) {
            return true;
        }

        // Published questionnaires are viewable by anyone
        return $questionnaire->status === QuestionnaireStatus::PUBLISHED->value;
    }

    /**
     * Determine whether the user can create questionnaires.
     */
    public function create($user): bool
    {
        // Default: authenticated users can create
        return true;
    }

    /**
     * Determine whether the user can update the questionnaire.
     */
    public function update($user, Questionnaire $questionnaire): bool
    {
        return $this->isOwner($user, $questionnaire);
    }

    /**
     * Determine whether the user can delete the questionnaire.
     */
    public function delete($user, Questionnaire $questionnaire): bool
    {
        return $this->isOwner($user, $questionnaire);
    }

    /**
     * Determine whether the user can publish the questionnaire.
     */
    public function publish($user, Questionnaire $questionnaire): bool
    {
        if (! $this->isOwner($user, $questionnaire)) {
            return false;
        }

        return $questionnaire->status === QuestionnaireStatus::DRAFT->value;
    }

    /**
     * Determine whether the user can close the questionnaire.
     */
    public function close($user, Questionnaire $questionnaire): bool
    {
        if (! $this->isOwner($user, $questionnaire)) {
            return false;
        }

        return $questionnaire->status === QuestionnaireStatus::PUBLISHED->value;
    }

    /**
     * Determine whether the user can view responses.
     */
    public function viewResponses($user, Questionnaire $questionnaire): bool
    {
        return $this->isOwner($user, $questionnaire);
    }

    /**
     * Determine whether the user can export responses.
     */
    public function exportResponses($user, Questionnaire $questionnaire): bool
    {
        return $this->isOwner($user, $questionnaire);
    }

    /**
     * Check if the user is the owner of the questionnaire.
     */
    protected function isOwner($user, Questionnaire $questionnaire): bool
    {
        return $user->getKey() === $questionnaire->user_id;
    }
}
