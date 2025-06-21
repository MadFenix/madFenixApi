<?php
namespace App\Modules\Theme\Domain;

use App\Modules\Base\Domain\BaseDomain;

class Theme extends BaseDomain
{
    const VALIDATION_CONTEXT = [
        'name' => ['nullable', 'string'],
        'logo' => ['nullable', 'string'],
        'color_primary' => ['nullable', 'string'],
        'color_secondary' => ['nullable', 'string'],
        'color_white0' => ['nullable', 'string'],
        'color_black' => ['nullable', 'string'],
        'color_background_secondary' => ['nullable', 'string'],
        'title_profile' => ['nullable', 'string'],
        'title_avatar' => ['nullable', 'string'],
        'title_state' => ['nullable', 'string'],
        'title_coin_premium' => ['nullable', 'string'],
        'title_coin_free' => ['nullable', 'string'],
        'title_polls' => ['nullable', 'string'],
        'title_items' => ['nullable', 'string'],
        'title_collections' => ['nullable', 'string'],
        'title_season' => ['nullable', 'string'],
        'title_season_level' => ['nullable', 'string'],
        'title_season_points' => ['nullable', 'string'],
        'title_season_premium' => ['nullable', 'string'],
        'title_store' => ['nullable', 'string'],
        'title_store_h1' => ['nullable', 'string'],
        'active' => ['nullable', 'integer'],
    ];

    protected $fillable = [
        'name',
        'logo',
        'color_primary',
        'color_secondary',
        'color_white0',
        'color_black',
        'color_background_secondary',
        'title_profile',
        'title_avatar',
        'title_state',
        'title_coin_premium',
        'title_coin_free',
        'title_polls',
        'title_items',
        'title_collections',
        'title_season',
        'title_season_level',
        'title_season_points',
        'title_season_premium',
        'title_store',
        'title_store_h1',
        'active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'active' => 'integer',
    ];

    // GETTERS

    public function getValidationContext(): array
    {
        return self::VALIDATION_CONTEXT;
    }

    public function getIcon(): string
    {
        return 'palette';
    }

    // Others

    public function remove(): bool
    {
        return $this->delete();
    }

    /**
     * Activate this theme and deactivate all others
     *
     * @return bool
     */
    public function activate(): bool
    {
        // Deactivate all themes
        self::query()->update(['active' => 0]);

        // Activate this theme
        $this->active = 1;
        return $this->save();
    }
}
