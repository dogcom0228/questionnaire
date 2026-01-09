<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    public function getTable(): string
    {
        $tableName = config('questionnaire.table_names.questions', 'questions');
        
        // Validate table name to prevent SQL injection
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            throw new \InvalidArgumentException('Invalid table name: ' . $tableName);
        }
        
        return $tableName;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'questionnaire_id',
        'type',
        'content',
        'description',
        'options',
        'required',
        'order',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'options' => 'array',
        'settings' => 'array',
        'required' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the questionnaire that owns the question.
     */
    public function questionnaire(): BelongsTo
    {
        $questionnaireModel = config('questionnaire.models.questionnaire', Questionnaire::class);
        return $this->belongsTo($questionnaireModel);
    }

    /**
     * Get the answers for the question.
     */
    public function answers(): HasMany
    {
        $answerModel = config('questionnaire.models.answer', Answer::class);
        return $this->hasMany($answerModel);
    }

    /**
     * Get the question type handler.
     */
    public function getTypeHandler()
    {
        $registry = app(\Liangjin0228\Questionnaire\Contracts\QuestionTypeRegistryInterface::class);
        return $registry->get($this->type);
    }

    /**
     * Scope a query to order by the order column.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Scope a query to only include required questions.
     */
    public function scopeRequired($query)
    {
        return $query->where('required', true);
    }
}
