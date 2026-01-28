<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Question\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $questionnaire_id
 * @property string $type
 * @property string $content
 * @property string|null $description
 * @property array<int|string, mixed>|null $options
 * @property bool $required
 * @property int $order
 * @property array<string, mixed>|null $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Liangjin0228\Questionnaire\Database\Factories\QuestionFactory factory($count = null, $state = [])
 */
class Question extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    public function getTable(): string
    {
        /** @var mixed $configValue */
        $configValue = config('questionnaire.table_names.questions', 'questions');
        $tableName = is_string($configValue) ? $configValue : 'questions';

        // Validate table name to prevent SQL injection
        if (! preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            throw new \InvalidArgumentException('Invalid table name: '.$tableName);
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
     *
     * @return BelongsTo<\Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire, $this>
     */
    public function questionnaire(): BelongsTo
    {
        /** @var class-string<\Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire> $questionnaireModel */
        $questionnaireModel = config('questionnaire.models.questionnaire', \Liangjin0228\Questionnaire\Domain\Questionnaire\Models\Questionnaire::class);

        return $this->belongsTo($questionnaireModel);
    }

    /**
     * Get the answers for the question.
     *
     * @return HasMany<\Liangjin0228\Questionnaire\Domain\Response\Models\Answer, $this>
     */
    public function answers(): HasMany
    {
        /** @var class-string<\Liangjin0228\Questionnaire\Domain\Response\Models\Answer> $answerModel */
        $answerModel = config('questionnaire.models.answer', \Liangjin0228\Questionnaire\Domain\Response\Models\Answer::class);

        return $this->hasMany($answerModel);
    }

    // getTypeHandler() removed in favor of QuestionTypeStrategy

    /**
     * Scope a query to order by the order column.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Scope a query to only include required questions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeRequired($query)
    {
        return $query->where('required', true);
    }
}
