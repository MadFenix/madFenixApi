<?php

namespace App\Modules\Universe\Character\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;

class CategoryField extends BaseTransformer
{
    public function toArray($request)
    {
        return array_merge(parent::toArray($request), [
            'character_id' => $this->character_id,
            'key' => $this->key,
            'value' => $this->value,
            'character' => $this->whenLoaded('character'),
        ]);
    }
}
