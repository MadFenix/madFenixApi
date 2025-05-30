<?php
namespace App\Modules\Game\Profile\Domain;

use App\Modules\Base\Domain\BaseDomain;

class Profile extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'user_id' => ['required', 'integer', 'exists:users,id'],
        'description' => ['required', 'string', 'min:4', 'max:255'],
        'details' => ['required', 'string', 'min:8', 'max:2000'],
        'avatar' => ['required', 'string', 'min:4', 'max:255'],
        'plumas_hedera' => ['integer'],
        'plumas' => ['integer'],
        'season_level' => ['integer'],
        'season_points' => ['integer'],
        'oro_hedera' => ['integer'],
        'oro' => ['integer'],
        'twitch_user_id' => ['string'],
        'twitch_user_name' => ['string'],
        'twitch_api_user_token' => ['string'],
        'twitch_api_user_refresh_token' => ['string'],
        'twitch_scope' => ['string'],
        'steam_user_id' => ['string'],
        'steam_user_name' => ['string'],
    ];

    protected $fillable = [
        'description',
        'details',
        'avatar',
        'plumas_hedera',
        'plumas',
        'oro_hedera',
        'oro',
        'season_level',
        'season_points',
        'twitch_user_id',
        'twitch_user_name',
        'twitch_api_user_token',
        'twitch_api_user_refresh_token',
        'twitch_scope',
        'steam_user_id',
        'steam_user_name',
        'creator_id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    // RELATIONS

    public function user()
    {
        return $this->belongsTo('App\Modules\User\Domain\User', 'user_id');
    }

    // GETTERS

    public function getValidationContext(): array
    {
        return self::VALIDATION_COTNEXT;
    }

    public function getIcon(): string
    {
        return 'user';
    }

    // Others

    public function remove(): bool
    {
        return $this->delete();
    }
}
