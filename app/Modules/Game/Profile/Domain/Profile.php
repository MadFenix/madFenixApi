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
        'plumas' => ['integer'],
    ];

    protected $fillable = [
        'description',
        'details',
        'avatar',
        'plumas',
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
