<?php

namespace App\Modules\Game\Node\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Game\Blueprint\Transformers\Blueprint;
use App\Modules\Game\Node\Domain\Node as NodeModel;

class Node extends BaseTransformer
{
    /**
     * The resource instance.
     *
     * @var mixed|NodeModel
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
            'blueprint' => new Blueprint($this->blueprint),
            'blueprint_id' => $this->blueprint_id,
            'owner' => new BaseTransformer($this->owner),
            'owner_id' => $this->owner_id,
            'translation_x' => $this->translation_x,
            'translation_y' => $this->translation_y,
            'translation_z' => $this->translation_z,
            'rotation_x' => $this->rotation_x,
            'rotation_y' => $this->rotation_y,
            'rotation_z' => $this->rotation_z,
            'scale_x' => $this->scale_x,
            'scale_y' => $this->scale_y,
            'scale_z' => $this->scale_z,
        ];
    }
}
