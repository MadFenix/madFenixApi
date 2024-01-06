<?php
namespace App\Modules\Game\Node\Domain;

use App\Modules\Base\Domain\BaseDomain;

class Node extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'creator_id' => ['required', 'integer', 'exists:users,id'],
        'blueprint_id' => ['required', 'integer', 'exists:blueprints,id'],
        'owner_id' => ['required', 'integer', 'exists:users,id'],
        'description' => ['required', 'string', 'min:4', 'max:255'],
        'details' => ['required', 'string', 'min:8', 'max:2000'],
        'translation_x' => ['required', 'numeric', 'between:-10000000.999,10000000.999'],
        'translation_y' => ['required', 'numeric', 'between:-10000000.999,10000000.999'],
        'translation_z' => ['required', 'numeric', 'between:-10000000.999,10000000.999'],
        'rotation_x' => ['required', 'numeric', 'between:-10000000.999,10000000.999'],
        'rotation_y' => ['required', 'numeric', 'between:-10000000.999,10000000.999'],
        'rotation_z' => ['required', 'numeric', 'between:-10000000.999,10000000.999'],
        'scale_x' => ['required', 'numeric', 'between:-10000000.999,10000000.999'],
        'scale_y' => ['required', 'numeric', 'between:-10000000.999,10000000.999'],
        'scale_z' => ['required', 'numeric', 'between:-10000000.999,10000000.999'],
    ];

    protected $fillable = [
        'description',
        'details',
        'translation_x',
        'translation_y',
        'translation_z',
        'rotation_x',
        'rotation_y',
        'rotation_z',
        'scale_x',
        'scale_y',
        'scale_z',
        'creator_id',
        'blueprint_id',
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
        return 'cube';
    }

    // Others

    public function remove(): bool
    {
        return $this->delete();
    }
}
