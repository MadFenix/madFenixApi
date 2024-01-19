<?php

namespace App\Modules\Game\Profile\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Game\Profile\Domain\Profile as ProfileModel;

class Profile extends BaseTransformer
{
    /**
     * The resource instance.
     *
     * @var mixed|ProfileModel
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
            'details' => $this->details,
            'avatar' => $this->avatar,
            'user' => new BaseTransformer($this->user),
            'plumas' => new BaseTransformer($this->plumas),
        ];
    }
}
