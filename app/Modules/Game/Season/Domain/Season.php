<?php
namespace App\Modules\Game\Season\Domain;

use App\Modules\Base\Domain\BaseDomain;

class Season extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'name' => ['required', 'string'],
        'max_level' => ['required', 'integer'],
        'max_points' => ['required', 'integer'],
        'start_date' => ['required', 'date'],
        'end_date' => ['required', 'date'],
    ];

    protected $fillable = [
        'name',
        'max_level',
        'max_points',
        'start_date',
        'end_date',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    // RELATIONS

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
