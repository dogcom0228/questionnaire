<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\ReadModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Read Model for Response projections.
 *
 * Rebuilt from ResponseSubmitted events. Do NOT use for writes.
 *
 * @property int $id
 * @property string $uuid
 * @property int $questionnaire_id
 * @property int|null $user_id
 * @property string|null $session_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property array<string, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $submitted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read QuestionnaireModel $questionnaire
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AnswerModel> $answers
 */
class ResponseModel extends Model
{
    public function getTable(): string
    {
        /** @var mixed $configValue */
        $configValue = config('questionnaire.table_names.responses', 'responses');
        $tableName = is_string($configValue) ? $configValue : 'responses';

        if (! preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            throw new \InvalidArgumentException('Invalid table name: '.$tableName);
        }

        return $tableName;
    }

    protected $fillable = [
        'uuid',
        'questionnaire_id',
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'metadata',
        'submitted_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'submitted_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<QuestionnaireModel, $this>
     */
    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireModel::class, 'questionnaire_id');
    }

    /**
     * @return HasMany<AnswerModel, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(AnswerModel::class, 'response_id');
    }
}
