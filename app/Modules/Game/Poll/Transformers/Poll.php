<?php

namespace App\Modules\Blockchain\Wallet\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Blockchain\Wallet\Domain\Wallet as WalletModel;

class Poll extends BaseTransformer
{
    /**
     * The resource instance.
     *
     * @var mixed|WalletModel
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
            'user' => new BaseTransformer($this->user),
            'portrait_image' => $this->portrait_image,
            'featured_image' => $this->featured_image,
            'answers' => $this->answers,
            'start_date' => $this->start_date->format('Y-m-d H:i:s'),
            'end_date' => $this->end_date->format('Y-m-d H:i:s'),
        ];
    }
}
