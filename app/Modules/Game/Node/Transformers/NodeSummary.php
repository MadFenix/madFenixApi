<?php

namespace App\Modules\Game\Node\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Game\Node\Domain\Node as NodeModel;

class NodeSummary extends BaseTransformer
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
            'id' => $this->id,
            'scn' => $this->blueprint->scene,
            't_x' => $this->translation_x,
            't_y' => $this->translation_y,
            't_z' => $this->translation_z,
            'r_x' => $this->rotation_x,
            'r_y' => $this->rotation_y,
            'r_z' => $this->rotation_z,
            's_x' => $this->scale_x,
            's_y' => $this->scale_y,
            's_z' => $this->scale_z,
        ];
    }
}
