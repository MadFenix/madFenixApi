<?php
namespace App\Modules\Twitch\Domain;

use App\Modules\Base\Domain\BaseDomain;

class TwitchRewardRedemption extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'twitch_reward_id' => ['required', 'integer', 'exists:twitch_rewards,id'],
        'user_id' => ['required', 'integer', 'exists:users,id'],
        'twitch_api_reward_redemption_id' => ['required', 'string'],
    ];

    protected $fillable = [
        'twitch_reward_id',
        'user_id',
        'twitch_api_reward_redemption_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    // RELATIONS

    public function twitch_reward()
    {
        return $this->belongsTo('App\Modules\Twitch\Domain\TwitchReward', 'twitch_reward_id');
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
