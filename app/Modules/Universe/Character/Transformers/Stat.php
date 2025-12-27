<?php

namespace App\Modules\Universe\Character\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;

class Stat extends BaseTransformer
{
    public function toArray($request)
    {
        return array_merge(parent::toArray($request), [
            'character_id' => $this->character_id,
            'hp' => $this->hp,
            'ad' => $this->ad,
            'ap' => $this->ap,
            'def' => $this->def,
            'mr' => $this->mr,
            'stat_cap' => $this->stat_cap,
            'uses_magic' => $this->uses_magic,
            'character' => $this->whenLoaded('character'),
        ]);
    }
}
