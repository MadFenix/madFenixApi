<?php

namespace App\Modules\Universe\Character\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;

class Rank extends BaseTransformer
{
    public function toArray($request)
    {
        return array_merge(parent::toArray($request), [
            'name' => $this->name,
        ]);
    }
}
