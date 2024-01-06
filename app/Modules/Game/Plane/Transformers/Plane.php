<?php

namespace App\Modules\Game\Plane\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Game\Plane\Domain\Plane as PlaneModel;

class Plane extends BaseTransformer
{
    /**
     * The resource instance.
     *
     * @var mixed|PlaneModel
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
            'details' => $this->details,
            'creator' => new BaseTransformer($this->creator),
            'scene' => $this->scene,
        ];
    }
}
