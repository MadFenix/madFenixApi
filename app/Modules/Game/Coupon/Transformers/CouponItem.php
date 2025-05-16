<?php

namespace App\Modules\Game\Coupon\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Game\Coupon\Domain\CouponItem as CouponItemModel;

class CouponItem extends BaseTransformer
{
    /**
     * The resource instance.
     *
     * @var mixed|CouponItemModel
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
            'nft_id' => $this->nft_id,
            'rarity' => $this->rarity,
            'tags' => $this->tags,
            'nft_serial_greater_equal' => $this->nft_serial_greater_equal,
            'nft_serial_less_equal' => $this->nft_serial_less_equal,
            'start_date' => $this->start_date->format('Y-m-d H:i:s'),
            'end_date' => $this->end_date->format('Y-m-d H:i:s'),
        ]);
    }
}
