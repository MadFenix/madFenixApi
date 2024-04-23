<?php

namespace App\Modules\Game\Season\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Game\Profile\Domain\Season as ProfileModel;

class Season extends BaseTransformer
{
    /**
     * The resource instance.
     *
     * @var mixed|ProfileModel
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
            'game' => $this->game,
            'fase' => $this->fase,
            'user' => new BaseTransformer($this->user),
        ];
    }
}
