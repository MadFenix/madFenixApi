<?php
namespace App\Modules\Game\Ranking\Domain;

use App\Modules\Base\Domain\BaseDomain;

class Ranking extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'user_id' => ['required', 'integer', 'exists:users,id'],
        'game' => ['required', 'string', 'min:4', 'max:255'],
        'network_group' => ['required', 'string', 'min:4', 'max:255'],
        'fase' => ['required', 'integer'],
    ];

    protected $fillable = [
        'user_id',
        'game',
        'network_group',
        'fase'
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
