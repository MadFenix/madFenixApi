<?php
namespace App\Modules\Store\Domain;

use App\Modules\Base\Domain\BaseDomain;

class ProductOrder extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'product_id' => ['required', 'integer', 'exists:products,id'],
        'user_id' => ['required', 'integer', 'exists:users,id'],
        'payment_validated' => ['nullable', 'integer'],
    ];

    protected $fillable = [
        'product_id',
        'user_id',
        'payment_validated',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    // RELATIONS

    public function product()
    {
        return $this->belongsTo('App\Modules\Store\Domain\Product', 'product_id');
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
