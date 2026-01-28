<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Domain\Questionnaire\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liangjin0228\Questionnaire\Database\Factories\QuestionnaireFactory;
use Liangjin0228\Questionnaire\Domain\Questionnaire\Enums\QuestionnaireStatus;

/**
 * @property int $id
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
 *
 * @method static \Liangjin0228\Questionnaire\Database\Factories\QuestionnaireFactory factory($count = null, $state = [])
 */
class Questionnaire extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (Questionnaire $questionnaire) {
            if (empty($questionnaire->slug)) {
                $questionnaire->slug = \Illuminate\Support\Str::slug($questionnaire->title ?? '') ?: \Illuminate\Support\Str::random(10);
            }
        });
    }

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
     * @return HasMany<\Liangjin0228\Questionnaire\Domain\Question\Models\Question, $this>
     */
    public function questions(): HasMany
    {
        /** @var class-string<\Liangjin0228\Questionnaire\Domain\Question\Models\Question> $questionModel */
        $questionModel = config('questionnaire.models.question', \Liangjin0228\Questionnaire\Domain\Question\Models\Question::class);

        return $this->hasMany($questionModel)->orderBy('order');
    }

    /**
     * Get the responses for the questionnaire.
     *
     * @return HasMany<\Liangjin0228\Questionnaire\Domain\Response\Models\Response, $this>
     */
    public function responses(): HasMany
    {
        /** @var class-string<\Liangjin0228\Questionnaire\Domain\Response\Models\Response> $responseModel */
        $responseModel = config('questionnaire.models.response', \Liangjin0228\Questionnaire\Domain\Response\Models\Response::class);

        return $this->hasMany($responseModel);
    }

    /**
     * Get the user that owns the questionnaire.
     *
     * @return BelongsTo<\Illuminate\Foundation\Auth\User, $this>
     */
    public function user(): BelongsTo
    {
        /** @var class-string<\Illuminate\Foundation\Auth\User> $userModel */
        $userModel = config('questionnaire.models.user') ?? config('auth.providers.users.model') ?? 'App\\Models\\User';

        return $this->belongsTo($userModel);
    }

    /**
     * Determine if the questionnaire is active (published and within date range).
     */
    public function getIsActiveAttribute(): bool
    {
        if ($this->status !== QuestionnaireStatus::PUBLISHED->value) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the questionnaire is accepting responses.
     */
    public function getIsAcceptingResponsesAttribute(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->submission_limit !== null) {
            return $this->responses()->count() < $this->submission_limit;
        }

        return true;
    }

    /**
     * Scope a query to only include published questionnaires.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopePublished($query)
    {
        return $query->where('status', QuestionnaireStatus::PUBLISHED->value);
    }

    /**
     * Scope a query to only include active questionnaires.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeActive($query)
    {
        return $query->published()
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    /**
     * Scope a query to only include questionnaires for a specific user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @param  int|string  $userId
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return config('questionnaire.routes.use_slug', false) ? 'slug' : 'id';
    }

    /**
     * Get available statuses.
     *
     * @return array<string, string>
     */
    public static function getStatuses(): array
    {
        return array_combine(
            array_map(fn (QuestionnaireStatus $status) => $status->value, QuestionnaireStatus::cases()),
            array_map(fn (QuestionnaireStatus $status) => $status->label(), QuestionnaireStatus::cases())
        );
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return QuestionnaireFactory
     */
    protected static function newFactory()
    {
        return \Liangjin0228\Questionnaire\Database\Factories\QuestionnaireFactory::new();
    }
}
