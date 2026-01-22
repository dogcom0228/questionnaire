<?php

namespace Liangjin0228\Questionnaire\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuestionTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this['id'] ?? $this->resource['id'], // Handle array or object
            'name' => $this['name'] ?? $this->resource['name'],
            'description' => $this['description'] ?? $this->resource['description'],
            'icon' => $this['icon'] ?? $this->resource['icon'],
            'rules' => $this['rules'] ?? $this->resource['rules'] ?? [],
        ];
    }
}
