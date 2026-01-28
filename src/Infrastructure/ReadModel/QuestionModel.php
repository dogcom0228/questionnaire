<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\ReadModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Read Model for Question projections.
 *
 * This is the read-side representation rebuilt from events via Projectors.
 * Do NOT use for write operations.
 *
 * @property int $id
 * @property string $uuid
 * @property int $questionnaire_id
 * @property string $type
 * @property string $content
 * @property string|null $description
 * @property array<string, mixed>|null $options
 * @property bool $required
 * @property int $order
 * @property array<string, mixed>|null $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read QuestionnaireModel $questionnaire
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AnswerModel> $answers
 */
class QuestionModel extends Model
{
    public function getTable(): string
    {
        /** @var mixed $configValue */
        $configValue = config('questionnaire.table_names.questions', 'questions');
        $tableName = is_string($configValue) ? $configValue : 'questions';

        if (! preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            throw new \InvalidArgumentException('Invalid table name: '.$tableName);
        }

        return $tableName;
    }

    protected $fillable = [
        'uuid',
        'questionnaire_id',
        'type',
        'content',
        'description',
        'options',
        'required',
        'order',
        'settings',
    ];

    protected $casts = [
        'options' => 'array',
        'settings' => 'array',
        'required' => 'boolean',
        'order' => 'integer',
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
        return $this->hasMany(AnswerModel::class, 'question_id');
    }
}
