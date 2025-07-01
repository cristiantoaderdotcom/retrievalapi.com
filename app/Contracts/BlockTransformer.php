<?php

namespace App\Contracts;

use Illuminate\Http\Resources\Json\JsonResource;

interface BlockTransformer {
	public function transform(JsonResource $resource): array;
}
