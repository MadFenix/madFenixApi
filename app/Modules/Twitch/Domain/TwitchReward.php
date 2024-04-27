<?php
namespace App\Modules\Twitch\Domain;

use App\Modules\Base\Domain\BaseDomain;

class TwitchReward extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'user_id' => ['required', 'integer', 'exists:users,id'],
        'twitch_api_reward_id' => ['required', 'string'],
        'name' => ['required', 'string'],
        'points' => ['required', 'string'],
    ];

    protected $fillable = [
        'user_id',
        'twitch_api_reward_id',
        'name',
        'points',
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
