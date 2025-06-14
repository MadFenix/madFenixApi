<?php

namespace App\Modules\Game\Season\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Game\Season\Domain\SeasonReward as SeasonRewardModel;

class SeasonReward extends BaseTransformer
{
    /**
     * The resource instance.
     *
     * @var mixed|SeasonRewardModel
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
            'level' => $this->level,
            'required_points' => $this->required_points,
            'oro' => $this->oro,
            'plumas' => $this->plumas,
            'nft_id' => $this->nft_id,
            'max_nft_rewards' => $this->max_nft_rewards,
            'custom_reward' => $this->custom_reward,
            'season_id' => $this->season_id,
        ]);
    }
}
