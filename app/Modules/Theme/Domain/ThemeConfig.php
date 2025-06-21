<?php
namespace App\Modules\Theme\Domain;

use App\Modules\Base\Domain\BaseDomain;

class ThemeConfig extends BaseDomain
{
    const VALIDATION_CONTEXT = [
        'name' => ['nullable', 'string'],
        'name_ecosystem' => ['nullable', 'string'],
        'name_fiscal' => ['nullable', 'string'],
        'direction' => ['nullable', 'string'],
        'city' => ['nullable', 'string'],
        'postal_code' => ['nullable', 'string'],
        'state' => ['nullable', 'string'],
        'email' => ['nullable', 'email'],
        'legal_advise' => ['nullable', 'string'],
        'terms_conditions' => ['nullable', 'string'],
        'cookies_policy' => ['nullable', 'string'],
        'privacy_policy' => ['nullable', 'string'],
        'active_coin_free' => ['nullable', 'integer'],
        'active_coin_premium' => ['nullable', 'integer'],
        'active_polls' => ['nullable', 'integer'],
        'active_seasons' => ['nullable', 'integer'],
        'active_items' => ['nullable', 'integer'],
        'active_coupons' => ['nullable', 'integer'],
        'active_store' => ['nullable', 'integer'],
        'active_referrals' => ['nullable', 'integer'],
        'active_hedera' => ['nullable', 'integer'],
        'active' => ['nullable', 'integer'],
    ];

    protected $fillable = [
        'name',
        'name_ecosystem',
        'name_fiscal',
        'direction',
        'city',
        'postal_code',
        'state',
        'email',
        'legal_advise',
        'terms_conditions',
        'cookies_policy',
        'privacy_policy',
        'active_coin_free',
        'active_coin_premium',
        'active_polls',
        'active_seasons',
        'active_items',
        'active_coupons',
        'active_store',
        'active_referrals',
        'active_hedera',
        'active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'active_coin_free' => 'integer',
        'active_coin_premium' => 'integer',
        'active_polls' => 'integer',
        'active_seasons' => 'integer',
        'active_items' => 'integer',
        'active_coupons' => 'integer',
        'active_store' => 'integer',
        'active_referrals' => 'integer',
        'active_hedera' => 'integer',
        'active' => 'integer',
    ];

    // GETTERS

    public function getValidationContext(): array
    {
        return self::VALIDATION_CONTEXT;
    }

    public function getIcon(): string
    {
        return 'settings';
    }

    // Others

    public function remove(): bool
    {
        return $this->delete();
    }

    /**
     * Activate this configuration and deactivate all others
     *
     * @return bool
     */
    public function activate(): bool
    {
        // Deactivate all configurations
        self::query()->update(['active' => 0]);

        // Activate this configuration
        $this->active = 1;
        return $this->save();
    }
}
