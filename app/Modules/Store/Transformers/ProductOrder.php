<?php

namespace App\Modules\Store\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Store\Domain\ProductOrder as ProductOrderModel;
use App\Modules\User\Transformers\User;

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
            'product' => (array) (new Product($this->product))->toArray($request),
            'user_id' => $this->user_id,
            'user' => (array) (new User($this->user))->toArray($request),
            'payment_validated' => $this->payment_validated,
            'is_gift' => $this->is_gift,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
    }
}
