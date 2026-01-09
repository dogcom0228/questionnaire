<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_CLOSED = 'closed';

    /**
     * The table associated with the model.
     */
    public function getTable(): string
    {
        $tableName = config('questionnaire.table_names.questionnaires', 'questionnaires');
        
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
     */
    public function questions(): HasMany
    {
        $questionModel = config('questionnaire.models.question', Question::class);
        return $this->hasMany($questionModel)->orderBy('order');
    }

    /**
     * Get the responses for the questionnaire.
     */
    public function responses(): HasMany
    {
        $responseModel = config('questionnaire.models.response', Response::class);
        return $this->hasMany($responseModel);
    }

    /**
     * Get the user that owns the questionnaire.
     */
    public function user(): BelongsTo
    {
        $userModel = config('questionnaire.models.user', config('auth.providers.users.model', 'App\\Models\\User'));
        return $this->belongsTo($userModel);
    }

    /**
     * Determine if the questionnaire is active (published and within date range).
     */
    public function getIsActiveAttribute(): bool
    {
        if ($this->status !== self::STATUS_PUBLISHED) {
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
        if (!$this->is_active) {
            return false;
        }

        if ($this->submission_limit !== null) {
            return $this->responses()->count() < $this->submission_limit;
        }

        return true;
    }

    /**
     * Scope a query to only include published questionnaires.
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Scope a query to only include active questionnaires.
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
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_CLOSED => 'Closed',
        ];
    }
}
