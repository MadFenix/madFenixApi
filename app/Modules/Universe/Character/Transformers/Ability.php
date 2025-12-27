<?php

namespace App\Modules\Universe\Character\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;

class Ability extends BaseTransformer
{
    public function toArray($request)
    {
        return array_merge(parent::toArray($request), [
            'character_id' => $this->character_id,
            'name' => $this->name,
            'affinity_base' => $this->affinity_base,
            'aptitude_type' => $this->aptitude_type,
            'cone_hint' => $this->cone_hint,
            'notes' => $this->notes,
            'character' => $this->whenLoaded('character'),
        ]);
    }
}
