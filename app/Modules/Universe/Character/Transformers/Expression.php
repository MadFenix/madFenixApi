<?php

namespace App\Modules\Universe\Character\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;

class Expression extends BaseTransformer
{
    public function toArray($request)
    {
        return array_merge(parent::toArray($request), [
            'character_id' => $this->character_id,
            'expression' => $this->expression,
            'kind' => $this->kind,
            'character' => $this->whenLoaded('character'),
        ]);
    }
}
