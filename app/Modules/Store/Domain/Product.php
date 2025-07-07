<?php
namespace App\Modules\Store\Domain;

use App\Modules\Base\Domain\BaseDomain;

class Product extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'name' => ['required', 'string'],
        'short_description' => ['nullable', 'string'],
        'description' => ['nullable', 'string'],
        'image' => ['nullable', 'string'],
        'price_fiat' => ['nullable'],
        'price_oro' => ['nullable', 'integer'],
        'price_plumas' => ['nullable', 'integer'],
        'active' => ['nullable', 'integer'],
        'product_parent_id' => ['nullable', 'integer', 'exists:products,id'],
        'oro' => ['nullable', 'integer'],
        'plumas' => ['nullable', 'integer'],
        'nft_id' => ['nullable', 'integer', 'exists:nfts,id'],
        'rarity' => ['nullable', 'string'],
        'tags' => ['nullable', 'string'],
        'nft_serial_greater_equal' => ['nullable', 'integer'],
        'nft_serial_less_equal' => ['nullable', 'integer'],
        'custom' => ['nullable', 'string'],
        'one_time_purchase' => ['nullable', 'integer'],
        'one_time_purchase_global' => ['nullable', 'integer'],
        'order' => ['nullable', 'string'],
    ];

    protected $fillable = [
        'name',
        'short_description',
        'description',
        'image',
        'price_fiat',
        'price_oro',
        'price_plumas',
        'active',
        'product_parent_id',
        'oro',
        'plumas',
        'nft_id',
        'rarity',
        'tags',
        'nft_serial_greater_equal',
        'nft_serial_less_equal',
        'custom',
        'one_time_purchase',
        'one_time_purchase_global',
        'order',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    // RELATIONS

    public function product_parent()
    {
        return $this->belongsTo('App\Modules\Store\Domain\Product', 'product_parent_id');
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
