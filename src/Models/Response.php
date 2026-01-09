<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Response extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    public function getTable(): string
    {
        return config('questionnaire.table_names.responses', 'questionnaire_responses');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'questionnaire_id',
        'respondent_type',
        'respondent_id',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the questionnaire that owns the response.
     */
    public function questionnaire(): BelongsTo
    {
        $questionnaireModel = config('questionnaire.models.questionnaire', Questionnaire::class);
        return $this->belongsTo($questionnaireModel);
    }

    /**
     * Get the respondent (polymorphic).
     */
    public function respondent(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the answers for the response.
     */
    public function answers(): HasMany
    {
        $answerModel = config('questionnaire.models.answer', Answer::class);
        return $this->hasMany($answerModel, 'questionnaire_response_id');
    }

    /**
     * Get all answers as a key-value array (question_id => value).
     *
     * @return array<int, mixed>
     */
    public function getAnswersArray(): array
    {
        return $this->answers->pluck('value', 'question_id')->toArray();
    }

    /**
     * Get a specific answer by question ID.
     */
    public function getAnswer(int $questionId): ?Answer
    {
        return $this->answers->firstWhere('question_id', $questionId);
    }

    /**
     * Check if response is from authenticated user.
     */
    public function isAuthenticated(): bool
    {
        return $this->respondent_type !== null && $this->respondent_id !== null;
    }
}
