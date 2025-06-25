<?php

namespace App\Modules\Theme\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Theme\Domain\ThemeConfig as ThemeConfigModel;

class ThemeConfig extends BaseTransformer
{
    /**
     * The resource instance.
     *
     * @var mixed|ThemeConfigModel
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
            'name_ecosystem' => $this->name_ecosystem,
            'slogan_ecosystem' => $this->slogan_ecosystem,
            'name_fiscal' => $this->name_fiscal,
            'direction' => $this->direction,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'state' => $this->state,
            'email' => $this->email,
            'legal_advise' => $this->legal_advise,
            'terms_conditions' => $this->terms_conditions,
            'cookies_policy' => $this->cookies_policy,
            'privacy_policy' => $this->privacy_policy,
            'url_instagram' => $this->url_instagram,
            'url_youtube' => $this->url_youtube,
            'url_twitch' => $this->url_twitch,
            'url_x' => $this->url_x,
            'active_coin_free' => $this->active_coin_free,
            'active_coin_premium' => $this->active_coin_premium,
            'active_polls' => $this->active_polls,
            'active_seasons' => $this->active_seasons,
            'active_items' => $this->active_items,
            'active_coupons' => $this->active_coupons,
            'active_store' => $this->active_store,
            'active_referrals' => $this->active_referrals,
            'active_hedera' => $this->active_hedera,
            'active' => $this->active,
        ]);
    }
}
