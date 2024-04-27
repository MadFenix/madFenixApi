<?php
namespace App\Modules\Steam\Domain;

use App\Modules\Base\Domain\BaseDomain;

class SteamGameRedemption extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'steam_game_id' => ['required', 'integer', 'exists:steam_games,id'],
        'user_id' => ['required', 'integer', 'exists:users,id'],
        'time_redemption' => ['required', 'integer'],
    ];

    protected $fillable = [
        'steam_game_id',
        'user_id',
        'time_redemption',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    // RELATIONS

    public function steam_game()
    {
        return $this->belongsTo('App\Modules\Steam\Domain\SteamGame', 'steam_game_id');
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
