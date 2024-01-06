<?php

namespace App\Modules\Blockchain\Wallet\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Blockchain\Wallet\Domain\Wallet as WalletModel;

class Wallet extends BaseTransformer
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
            'user_id' => $this->user->id,
            'balance' => $this->balance,
        ];
    }
}
