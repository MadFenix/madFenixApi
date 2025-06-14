<?php

namespace App\Modules\Blockchain\Block\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Blockchain\Block\Domain\NftIdentification as NftIdentificationModel;

class NftIdentification extends BaseTransformer
{
    /**
     * The resource instance.
     *
     * @var mixed|NftIdentificationModel
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
            'nft_identification' => $this->nft_identification,
            'nft_id' => $this->nft_id,
            'rarity' => $this->rarity,
            'tag_1' => $this->tag_1,
            'tag_2' => $this->tag_2,
            'tag_3' => $this->tag_3,
            'user_id' => $this->user_id,
            'user_id_hedera' => $this->user_id_hedera,
            'madfenix_ownership' => $this->madfenix_ownership,
        ]);
    }
}
