<?php

namespace App\Modules\Game\Coupon\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Game\Profile\Domain\Profile as ProfileModel;

class Coupon extends BaseTransformer
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
            'coupon' => $this->coupon,
            'uses' => $this->uses,
            'max_uses' => $this->max_uses,
            'start_date' => $this->start_date->format('Y-m-d H:i:s'),
            'end_date' => $this->end_date->format('Y-m-d H:i:s'),
        ];
    }
}
