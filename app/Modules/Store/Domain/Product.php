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
        'price_fiat' => ['nullable', 'float'],
        'price_oro' => ['nullable', 'integer'],
        'active' => ['nullable', 'integer'],
        'product_parent_id' => ['nullable', 'integer', 'exists:products,id'],
        'oro' => ['nullable', 'integer'],
        'plumas' => ['nullable', 'integer'],
        'nft_id' => ['nullable', 'integer', 'exists:nfts,id'],
        'custom' => ['nullable', 'string'],
    ];

    protected $fillable = [
        'name',
        'short_description',
        'description',
        'image',
        'price_fiat',
        'price_oro',
        'active',
        'product_parent_id',
        'oro',
        'plumas',
        'nft_id',
        'custom',
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
