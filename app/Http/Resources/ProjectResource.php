<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'columns' => ColumnResource::collection($this->whenLoaded('columns')),
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
