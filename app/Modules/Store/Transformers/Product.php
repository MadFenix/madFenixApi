<?php

namespace App\Modules\Store\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Store\Domain\Product as ProductModel;

class Product extends BaseTransformer
{
    /**
     * The resource instance.
     *
     * @var mixed|ProductModel
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
            'short_description' => $this->short_description,
            'description' => $this->description,
            'image' => $this->image,
            'price_fiat' => $this->price_fiat,
            'price_oro' => $this->price_oro,
            'price_plumas' => $this->price_plumas,
            'active' => $this->active,
            'product_parent_id' => $this->product_parent_id,
            'oro' => $this->oro,
            'plumas' => $this->plumas,
            'nft_id' => $this->nft_id,
            'rarity' => $this->rarity,
            'tags' => $this->tags,
            'nft_serial_greater_equal' => $this->nft_serial_greater_equal,
            'nft_serial_less_equal' => $this->nft_serial_less_equal,
            'custom' => $this->custom,
            'one_time_purchase' => $this->one_time_purchase,
            'one_time_purchase_global' => $this->one_time_purchase_global,
            'order' => $this->order,
        ]);
    }
}
