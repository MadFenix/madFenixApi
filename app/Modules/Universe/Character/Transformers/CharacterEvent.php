<?php

namespace App\Modules\Universe\Character\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;

class CharacterEvent extends BaseTransformer
{
    public function toArray($request)
    {
        return array_merge(parent::toArray($request), [
            'character_id' => $this->character_id,
            'event_id' => $this->event_id,
            'title' => $this->title,
            'description' => $this->description,
            'happened_at' => $this->happened_at,
            'sort_order' => $this->sort_order,
            'character' => $this->whenLoaded('character'),
        ]);
    }
}
