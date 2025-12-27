<?php

namespace App\Modules\Universe\Character\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;

class UndeadSuffix extends BaseTransformer
{
    public function toArray($request)
    {
        return array_merge(parent::toArray($request), [
            'value' => $this->value,
        ]);
    }
}
