<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PagamentoResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'status' => $this->status,
            'payment_type' => $this->payment_type,
            'data' => $this->data,
        ];
    }
}