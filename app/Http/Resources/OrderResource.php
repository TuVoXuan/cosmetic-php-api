<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
          'id' => $this->id,
          'user' => UserResource::make($this->whenLoaded('user')),
          'order_date' => $this->order_date,
          'total_amount' => $this->total_amount,
          'order_items' => $this->whenLoaded('orderItems'),
          'created_at' => $this->created_at,
          'updated_at' => $this->updated_at  
        ];
    }
}
