<?php
namespace App\Modules\Blockchain\Block\Domain;

use App\Modules\Base\Domain\BaseDomain;

class Nft extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'name' => ['required', 'string'],
        'short_description' => ['nullable', 'string'],
        'description' => ['nullable', 'string'],
        'portrait_image' => ['nullable', 'string'],
        'featured_image' => ['nullable', 'string'],
        'token_props' => ['required', 'integer'],
        'token_realm' => ['required', 'integer'],
        'token_number' => ['required', 'integer'],
    ];

    protected $fillable = [
        'name',
        'short_description',
        'description',
        'portrait_image',
        'featured_image',
        'token_props',
        'token_realm',
        'token_number',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    // RELATIONS

    // GETTERS

    public function getValidationContext(): array
    {
        return self::VALIDATION_COTNEXT;
    }

    public function getIcon(): string
    {
        return 'block';
    }

    // Others

    public function remove(): bool
    {
        return $this->delete();
    }
}
