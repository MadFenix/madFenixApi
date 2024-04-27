<?php
namespace App\Modules\Steam\Domain;

use App\Modules\Base\Domain\BaseDomain;

class SteamGame extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'user_id' => ['required', 'integer', 'exists:users,id'],
        'steam_api_game_id' => ['required', 'string'],
        'name' => ['required', 'string'],
        'time_minutes' => ['required', 'integer'],
    ];

    protected $fillable = [
        'user_id',
        'steam_api_game_id',
        'name',
        'time_minutes',
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
