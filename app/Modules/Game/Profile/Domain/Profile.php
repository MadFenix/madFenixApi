<?php
namespace App\Modules\Game\Profile\Domain;

use App\Modules\Base\Domain\BaseDomain;

class Profile extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'creator_id' => ['required', 'integer', 'exists:users,id'],
        'description' => ['required', 'string', 'min:4', 'max:255'],
        'details' => ['required', 'string', 'min:8', 'max:2000'],
        'plane_id' => ['required', 'integer', 'exists:planes,id'],
        'avatar' => ['required', 'string', 'min:4', 'max:255'],
    ];

    protected $fillable = [
        'description',
        'details',
        'plane_id',
        'avatar',
        'creator_id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    // RELATIONS

    public function creator()
    {
        return $this->belongsTo('App\Modules\User\Domain\User', 'creator_id');
    }

    public function plane()
    {
        return $this->belongsTo('App\Modules\Game\Plane\Domain\Plane', 'plane_id');
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
