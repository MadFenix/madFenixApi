<?php

namespace App\Modules\Store\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Store\Domain\ProductOrder as ProductOrderModel;

class ProductOrder extends BaseTransformer
{
    /**
     * The resource instance.
     *
     * @var mixed|ProductOrderModel
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
            'product_id' => $this->product_id,
            'user_id' => $this->user_id,
            'payment_validated' => $this->payment_validated,
            'is_gift' => $this->is_gift,
        ]);
    }
}
