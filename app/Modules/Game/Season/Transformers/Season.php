<?php

namespace App\Modules\Game\Poll\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Game\Season\Domain\Season as SeasonModel;

class Season extends BaseTransformer
{
    /**
     * The resource instance.
     *
     * @var mixed|SeasonModel
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
        return array_merge(parent::toArray($request),[
            'name' => $this->name,
            'max_level' => $this->max_level,
            'max_points' => $this->max_points,
            'start_date' => $this->start_date->format('Y-m-d H:i:s'),
            'end_date' => $this->end_date->format('Y-m-d H:i:s'),
        ]);
    }
}
