<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\ReadModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Read Model for Answer projections.
 *
 * Rebuilt from ResponseSubmitted events. Do NOT use for writes.
 *
 * @property int $id
 * @property string $uuid
 * @property int $response_id
 * @property int $question_id
 * @property array<string, mixed>|string|null $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read ResponseModel $response
 * @property-read QuestionModel $question
 */
class AnswerModel extends Model
{
    public function getTable(): string
    {
        /** @var mixed $configValue */
        $configValue = config('questionnaire.table_names.answers', 'answers');
        $tableName = is_string($configValue) ? $configValue : 'answers';

        if (! preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            throw new \InvalidArgumentException('Invalid table name: '.$tableName);
        }

        return $tableName;
    }

    protected $fillable = [
        'uuid',
        'response_id',
        'question_id',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * @return BelongsTo<ResponseModel, $this>
     */
    public function response(): BelongsTo
    {
        return $this->belongsTo(ResponseModel::class, 'response_id');
    }

    /**
     * @return BelongsTo<QuestionModel, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionModel::class, 'question_id');
    }
}
