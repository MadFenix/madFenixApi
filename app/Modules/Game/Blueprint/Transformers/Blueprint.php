<?php

namespace App\Modules\Game\Blueprint\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Game\Blueprint\Domain\Blueprint as BlueprintModel;

class Blueprint extends BaseTransformer
{
    /**
     * The resource instance.
     *
     * @var mixed|BlueprintModel
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
            'creator_id' => $this->creator->id,
            'owner' => new BaseTransformer($this->owner),
            'owner_id' => $this->owner->id,
            'scene' => $this->scene,
            'code' => $this->code,
        ];
    }
}
