<?php

namespace App\Modules\Theme\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Theme\Domain\Theme as ThemeModel;

class Theme extends BaseTransformer
{
    /**
     * The resource instance.
     *
     * @var mixed|ThemeModel
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
            'logo' => $this->logo,
            'color_primary' => $this->color_primary,
            'color_secondary' => $this->color_secondary,
            'color_white0' => $this->color_white0,
            'color_black' => $this->color_black,
            'color_background_secondary' => $this->color_background_secondary,
            'title_profile' => $this->title_profile,
            'title_avatar' => $this->title_avatar,
            'title_state' => $this->title_state,
            'title_coin_premium' => $this->title_coin_premium,
            'title_coin_free' => $this->title_coin_free,
            'title_polls' => $this->title_polls,
            'title_items' => $this->title_items,
            'title_collections' => $this->title_collections,
            'title_season' => $this->title_season,
            'title_season_level' => $this->title_season_level,
            'title_season_points' => $this->title_season_points,
            'title_season_premium' => $this->title_season_premium,
            'title_store' => $this->title_store,
            'title_store_h1' => $this->title_store_h1,
            'icon_polls' => $this->icon_polls,
            'icon_items' => $this->icon_items,
            'icon_coin_free' => $this->icon_coin_free,
            'icon_coin_premium' => $this->icon_coin_premium,
            'icon_rewards_pass' => $this->icon_rewards_pass,
            'active' => $this->active,
        ]);
    }
}
