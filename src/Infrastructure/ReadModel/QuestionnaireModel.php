<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Infrastructure\ReadModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Read Model for Questionnaire projections.
 *
 * This is the read-side representation rebuilt from events via Projectors.
 * Do NOT use for write operations - use the Questionnaire Aggregate instead.
 *
 * @property int $id
 * @property string $uuid
 * @property string $title
 * @property string|null $description
 * @property string $slug
 * @property string $status
 * @property array<string, mixed>|null $settings
 * @property \Illuminate\Support\Carbon|null $starts_at
 * @property \Illuminate\Support\Carbon|null $ends_at
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $closed_at
 * @property int|null $user_id
 * @property bool $requires_auth
 * @property int|null $submission_limit
 * @property string|null $duplicate_submission_strategy
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read bool $is_active
 * @property-read bool $is_accepting_responses
 * @property-read \Illuminate\Database\Eloquent\Collection<int, QuestionModel> $questions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ResponseModel> $responses
 */
class QuestionnaireModel extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     */
    public function getTable(): string
    {
        /** @var mixed $configValue */
        $configValue = config('questionnaire.table_names.questionnaires', 'questionnaires');
        $tableName = is_string($configValue) ? $configValue : 'questionnaires';

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
        'uuid',
        'title',
        'description',
        'slug',
        'status',
        'settings',
        'starts_at',
        'ends_at',
        'published_at',
        'closed_at',
        'user_id',
        'requires_auth',
        'submission_limit',
        'duplicate_submission_strategy',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'published_at' => 'datetime',
        'closed_at' => 'datetime',
        'requires_auth' => 'boolean',
        'submission_limit' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'is_active',
        'is_accepting_responses',
    ];

    /**
     * Get the questions for the questionnaire.
     *
     * @return HasMany<QuestionModel, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(QuestionModel::class, 'questionnaire_id')->orderBy('order');
    }

    /**
     * Get the responses for the questionnaire.
     *
     * @return HasMany<ResponseModel, $this>
     */
    public function responses(): HasMany
    {
        return $this->hasMany(ResponseModel::class, 'questionnaire_id');
    }

    /**
     * Check if the questionnaire is active.
     */
    public function getIsActiveAttribute(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->isBefore($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->isAfter($this->ends_at)) {
            return false;
        }

        return true;
    }

    /**
     * Check if the questionnaire is accepting responses.
     */
    public function getIsAcceptingResponsesAttribute(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->submission_limit !== null) {
            $responseCount = $this->responses()->count();

            if ($responseCount >= $this->submission_limit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get available statuses.
     *
     * @return array<int, string>
     */
    public static function getStatuses(): array
    {
        return ['draft', 'published', 'closed'];
    }
}
