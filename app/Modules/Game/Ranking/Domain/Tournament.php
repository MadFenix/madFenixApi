<?php
namespace App\Modules\Game\Ranking\Domain;

use App\Modules\Base\Domain\BaseDomain;

class Tournament extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'game' => ['required', 'string', 'min:4', 'max:255'],
        'name' => ['required', 'string', 'min:4', 'max:255'],
        'start_date' => ['required', 'date'],
        'end_date' => ['required', 'date'],
    ];

    protected $fillable = [
        'id',
        'game',
        'name',
        'start_date',
        'end_date'
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
