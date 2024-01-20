<?php
namespace App\Modules\Game\Ranking\Domain;

use App\Modules\Base\Domain\BaseDomain;

class TournamentUser extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'tournament_id' => ['required', 'integer', 'exists:tournaments,id'],
        'user_id' => ['required', 'integer', 'exists:users,id'],
        'max_points' => ['required', 'integer'],
        'max_time' => ['required', 'integer'],
    ];

    protected $fillable = [
        'id',
        'tournament_id',
        'user_id',
        'max_points',
        'max_time',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    // RELATIONS

    public function tournament()
    {
        return $this->belongsTo('App\Modules\Game\Ranking\Domain\Tournament', 'tournament_id');
    }

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
