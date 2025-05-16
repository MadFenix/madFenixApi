<?php

namespace App\Modules\Game\Coupon\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Game\Coupon\Domain\CouponGold as CouponGoldModel;

class CouponGold extends BaseTransformer
{
    /**
     * The resource instance.
     *
     * @var mixed|CouponGoldModel
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
            'coupon' => $this->coupon,
            'uses' => $this->uses,
            'max_uses' => $this->max_uses,
            'oro' => $this->oro,
            'start_date' => $this->start_date->format('Y-m-d H:i:s'),
            'end_date' => $this->end_date->format('Y-m-d H:i:s'),
        ]);
    }
}
