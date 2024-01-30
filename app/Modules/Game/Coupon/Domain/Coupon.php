<?php
namespace App\Modules\Game\Coupon\Domain;

use App\Modules\Base\Domain\BaseDomain;

class Coupon extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'coupon' => ['required', 'string', 'min:4', 'max:150'],
        'plumas' => ['integer'],
        'uses' => ['integer'],
        'max_uses' => ['integer'],
        'start_date' => ['required', 'date'],
        'end_date' => ['required', 'date'],
    ];

    protected $fillable = [
        'coupon',
        'plumas',
        'uses',
        'max_uses',
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
