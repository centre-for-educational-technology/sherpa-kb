<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AnswerResource extends JsonResource
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
            'descriptions' => $this->languages->keyBy('code')->map(function ($language) {
                return $language->pivot->description;
            }),
            'status' => [
                'value' => $this->status->getValue(),
                'status' => $this->status->status(),
                'transitionable' => $this->status->transitionableStates(),
            ],
            'date' => $this->created_at,
        ];
    }
}
