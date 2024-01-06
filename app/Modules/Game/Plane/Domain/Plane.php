<?php
namespace App\Modules\Game\Plane\Domain;

use App\Modules\Base\Domain\BaseDomain;

class Plane extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'creator_id' => ['required', 'integer', 'exists:users,id'],
        'description' => ['required', 'string', 'min:4', 'max:255'],
        'details' => ['required', 'string', 'min:8', 'max:2000'],
        'scene' => ['required', 'string', 'min:4', 'max:255'],
    ];

    protected $fillable = [
        'description',
        'details',
        'scene',
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

    public function insideProfiles()
    {
        return $this->hasMany('App\Modules\Game\Profile\Domain\Ranking', 'plane_id');
    }

    // GETTERS

    public function getValidationContext(): array
    {
        return self::VALIDATION_COTNEXT;
    }

    public function getIcon(): string
    {
        return 'cubes';
    }

    // Others

    public function remove(): bool
    {
        return $this->delete();
    }
}
