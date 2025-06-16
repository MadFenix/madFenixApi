<?php

namespace App\Modules\Game\Poll\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Game\Poll\Domain\Profile as ProfileModel;

class Profile extends BaseTransformer
{
    /**
     * The resource instance.
     *
     * @var mixed|PollModel
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
            'user_id' => $this->user_id,
            'description' => $this->description,
            'details' => $this->details,
            'avatar' => $this->avatar,
            'plumas_hedera' => $this->plumas_hedera,
            'plumas' => $this->plumas,
            'season_level' => $this->season_level,
            'season_points' => $this->season_points,
            'oro_hedera' => $this->oro_hedera,
            'oro' => $this->oro,
            'twitch_user_id' => $this->twitch_user_id,
            'twitch_user_name' => $this->twitch_user_name,
            'twitch_api_user_token' => $this->twitch_api_user_token,
            'twitch_api_user_refresh_token' => $this->twitch_api_user_refresh_token,
            'twitch_scope' => $this->twitch_scope,
            'steam_user_id' => $this->steam_user_id,
            'steam_user_name' => $this->steam_user_name,
        ]);
    }
}
