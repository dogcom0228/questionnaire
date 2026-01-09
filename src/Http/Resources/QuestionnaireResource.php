<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \Liangjin0228\Questionnaire\Models\Questionnaire $resource
 */
class QuestionnaireResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'description' => $this->resource->description,
            'status' => $this->resource->status,
            'requires_auth' => $this->resource->requires_auth,
            'is_accepting_responses' => $this->resource->is_accepting_responses,
            // We can add more fields or conditional relationships here
            'questions' => $this->whenLoaded('questions'),
            'created_at' => $this->resource->created_at?->toIso8601String(),
            'updated_at' => $this->resource->updated_at?->toIso8601String(),
        ];
    }
}
