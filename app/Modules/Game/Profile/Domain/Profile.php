<?php
namespace App\Modules\Game\Profile\Domain;

use App\Modules\Base\Domain\BaseDomain;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Profile extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'user_id' => ['required', 'integer', 'exists:users,id'],
        'description' => ['required', 'string', 'min:4', 'max:255'],
        'details' => ['nullable', 'string'],
        'avatar' => ['required', 'string', 'min:4', 'max:255'],
        'plumas_hedera' => ['integer'],
        'plumas' => ['integer'],
        'season_level' => ['integer'],
        'season_points' => ['integer'],
        'oro_hedera' => ['integer'],
        'oro' => ['integer'],
        'twitch_user_id' => ['nullable', 'string'],
        'twitch_user_name' => ['nullable', 'string'],
        'twitch_api_user_token' => ['nullable', 'string'],
        'twitch_api_user_refresh_token' => ['nullable', 'string'],
        'twitch_scope' => ['nullable', 'string'],
        'steam_user_id' => ['nullable', 'string'],
        'steam_user_name' => ['nullable', 'string'],
    ];

    protected $fillable = [
        'description',
        'details',
        'avatar',
        'plumas_hedera',
        'plumas',
        'season_level',
        'season_points',
        'season_premium',
        'oro_hedera',
        'oro',
        'hedera_wallet',
        'hedera_wallet_check',
        'hedera_wallet_check_account',
        'referred_code',
        'referred_code_from',
        'twitch_user_id',
        'twitch_user_name',
        'twitch_api_user_token',
        'twitch_api_user_refresh_token',
        'twitch_scope',
        'steam_user_id',
        'steam_user_name'
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
