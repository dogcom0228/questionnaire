<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    public function getTable(): string
    {
        return config('questionnaire.table_names.answers', 'questionnaire_answers');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'questionnaire_response_id',
        'question_id',
        'value',
    ];

    /**
     * Get the response that owns the answer.
     */
    public function response(): BelongsTo
    {
        $responseModel = config('questionnaire.models.response', Response::class);
        return $this->belongsTo($responseModel, 'questionnaire_response_id');
    }

    /**
     * Get the question that the answer belongs to.
     */
    public function question(): BelongsTo
    {
        $questionModel = config('questionnaire.models.question', Question::class);
        return $this->belongsTo($questionModel);
    }

    /**
     * Get the formatted value using the question type handler.
     */
    public function getFormattedValueAttribute(): string
    {
        $question = $this->question;
        if (!$question) {
            return (string) $this->value;
        }

        $handler = $question->getTypeHandler();
        if (!$handler) {
            return (string) $this->value;
        }

        return $handler->formatValue($this->value, $question);
    }

    /**
     * Get the parsed value (for array values stored as JSON).
     *
     * @return mixed
     */
    public function getParsedValueAttribute(): mixed
    {
        if (is_string($this->value)) {
            $decoded = json_decode($this->value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $this->value;
    }
}
