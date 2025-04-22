<?php
namespace App\Modules\Game\Coupon\Domain;

use App\Modules\Base\Domain\BaseDomain;

class CouponItem extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'coupon' => ['required', 'string', 'min:4', 'max:150'],
        'nft_id' => ['integer'],
        'rarity' => ['nullable', 'string'],
        'tags' => ['nullable', 'string'],
        'nft_serial_greater_equal' => ['nullable', 'integer'],
        'nft_serial_less_equal' => ['nullable', 'integer'],
        'uses' => ['integer'],
        'max_uses' => ['integer'],
        'start_date' => ['required', 'date'],
        'end_date' => ['required', 'date'],
    ];

    protected $fillable = [
        'coupon',
        'nft_id',
        'rarity',
        'tags',
        'nft_serial_greater_equal',
        'nft_serial_less_equal',
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
