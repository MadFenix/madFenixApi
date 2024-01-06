<?php

namespace App\Modules\Assistant\Thred\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;

class Thred extends BaseTransformer
{
    /**
     * The resource instance.
     *
     * @var mixed
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            $this->merge(parent::toArray($request)),
            'clipOutput' => $this->clipOutput,
        ];
    }
}
