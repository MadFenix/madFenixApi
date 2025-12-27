<?php

namespace App\Modules\Universe\Character\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;

class SubcategoryType extends BaseTransformer
{
    public function toArray($request)
    {
        return array_merge(parent::toArray($request), [
            'category_id' => $this->category_id,
            'name' => $this->name,
            'category' => $this->whenLoaded('category'),
            'definitions' => $this->whenLoaded('definitions'),
        ]);
    }
}
