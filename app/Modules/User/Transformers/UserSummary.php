<?php

namespace App\Modules\User\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use \App\Modules\Game\Profile\Transformers\Profile as ProfileTransformer;
use App\Modules\Game\Profile\Domain\Profile;
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
        $profile = Profile::where('user_id', $this->id)->first();

        return array_merge(parent::toArray($request), [
            'name' => $this->name,
            'email' => $this->email,
            'newsletter' => $this->newsletter,
            'profile_id' => $profile->id,
            'profile' => (new ProfileTransformer($profile))->toArray($request),
        ]);
    }
}
