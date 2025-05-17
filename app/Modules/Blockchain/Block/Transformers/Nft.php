<?php

namespace App\Modules\Blockchain\Block\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Blockchain\Block\Domain\Nft as NftModel;

class Nft extends BaseTransformer
{
    /**
     * The resource instance.
     *
     * @var mixed|NftModel
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
            'category' => $this->category,
            'subcategory' => $this->subcategory,
            'portrait_image' => $this->portrait_image,
            'featured_image' => $this->featured_image,
            'token_props' => $this->token_props,
            'token_realm' => $this->token_realm,
            'token_number' => $this->token_number,
        ]);
    }
}
