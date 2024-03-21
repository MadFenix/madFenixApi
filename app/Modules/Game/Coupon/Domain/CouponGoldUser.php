<?php
namespace App\Modules\Game\Coupon\Domain;

use App\Modules\Base\Domain\BaseDomain;

class CouponGoldUser extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'coupon_id' => ['required', 'integer', 'exists:coupons,id'],
        'user_id' => ['required', 'integer', 'exists:users,id'],
    ];

    protected $fillable = [
        'coupon_id',
        'user_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    // RELATIONS

    public function coupon()
    {
        return $this->belongsTo('App\Modules\Game\Coupon\Domain\CouponGold', 'coupon_id');
    }

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
