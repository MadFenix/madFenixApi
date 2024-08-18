<?php
namespace App\Modules\Game\Fighter\Domain;

use App\Modules\Base\Domain\BaseDomain;

class FighterFriend extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'user_id' => ['required', 'integer', 'exists:users,id'],
        'user_id_friend' => ['required', 'integer', 'exists:users,id'],
        'approved' => ['boolean'],
    ];

    protected $fillable = [
        'approved'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
    ];

    // RELATIONS

    public function user()
    {
        return $this->belongsTo('App\Modules\User\Domain\User', 'user_id');
    }

    public function userFriend()
    {
        return $this->belongsTo('App\Modules\User\Domain\User', 'user_id_friend');
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
