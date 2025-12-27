<?php

namespace App\Modules\Universe\Character\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;

class SubcategoryDefinition extends BaseTransformer
{
    public function toArray($request)
    {
        return array_merge(parent::toArray($request), [
            'subcategory_id' => $this->subcategory_id,
            'value' => $this->value,
            'subcategory_type' => $this->whenLoaded('subcategoryType'),
        ]);
    }
}
