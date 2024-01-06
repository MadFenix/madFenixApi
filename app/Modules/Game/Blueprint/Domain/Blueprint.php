<?php
namespace App\Modules\Game\Blueprint\Domain;

use App\Modules\Base\Domain\BaseDomain;

class Blueprint extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'creator_id' => ['required', 'integer', 'exists:users,id'],
        'owner_id' => ['required', 'integer', 'exists:users,id'],
        'description' => ['required', 'string', 'min:4', 'max:255'],
        'code' => ['nullable', 'string', 'min:8', 'max:25'],
        'details' => ['required', 'string', 'min:8', 'max:2000'],
        'scene' => ['required', 'string', 'min:4', 'max:255'],
    ];

    protected $fillable = [
        'description',
        'details',
        'code',
        'scene',
        'creator_id',
        'owner_id',
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

    public function owner()
    {
        return $this->belongsTo('App\Modules\User\Domain\User', 'owner_id');
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
