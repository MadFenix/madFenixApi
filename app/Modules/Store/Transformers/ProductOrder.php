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
        $user = new \stdClass();
        $user->id = $this->user_id;
        $user->email = $this->user->email;
        $user->name = $this->user->name;
        return array_merge(parent::toArray($request),[
            'product_id' => $this->product_id,
            'user' => $user,
            'payment_validated' => $this->payment_validated,
            'is_gift' => $this->is_gift,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
    }
}
