<?php

namespace App\Modules\Universe\Character\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;

class CharacterMedia extends BaseTransformer
{
    public function toArray($request)
    {
        return array_merge(parent::toArray($request), [
            'character_id' => $this->character_id,
            'media_type' => $this->media_type,
            'url' => $this->url,
            'alt_text' => $this->alt_text,
            'sort_order' => $this->sort_order,
            'character' => $this->whenLoaded('character'),
        ]);
    }
}
