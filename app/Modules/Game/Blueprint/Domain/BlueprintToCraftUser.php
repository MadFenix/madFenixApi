<?php
namespace App\Modules\Game\Blueprint\Domain;

use App\Modules\Base\Domain\BaseDomain;

class BlueprintToCraftUser extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'user_id' => ['required', 'integer', 'exists:users,id'],
        'blueprint_id' => ['required', 'integer', 'exists:blueprints,id'],
    ];

    protected $fillable = [
        'blueprint_id',
        'user_id',
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

    public function blueprint()
    {
        return $this->belongsTo('App\Modules\Game\Blueprint\Domain\Blueprint', 'blueprint_id');
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
