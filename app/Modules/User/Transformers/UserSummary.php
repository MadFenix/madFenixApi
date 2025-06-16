<?php

namespace App\Modules\User\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Event\Transformers\Event;
use App\Modules\User\Domain\User as UserModel;

class UserSummary extends BaseTransformer
{

    /**
     * The resource instance.
     *
     * @var mixed|UserModel
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
        return array_merge(parent::toArray($request), [
            'name' => $this->name,
            'email' => $this->email,
            'newsletter' => $this->newsletter,
        ]);
    }
}
