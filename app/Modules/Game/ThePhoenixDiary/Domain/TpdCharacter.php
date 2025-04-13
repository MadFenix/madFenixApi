<?php
namespace App\Modules\Game\ThePhoenixDiary\Domain;

use App\Modules\Base\Domain\BaseDomain;

class TpdCharacter extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'nft_id' => ['integer'],
        'initial_object_id' => ['integer'],
        'type' => ['string'],
        'subtype' => ['string'],
        'active' => ['integer'],
        'name' => ['string'],
        'short_description' => ['string'],
        'description' => ['string'],
        'portrait_image' => ['string'],
        'featured_image' => ['string'],
        'tpd_entry_url' => ['string'],
        'hp' => ['integer'],
        'ad' => ['integer'],
        'ap' => ['integer'],
        'def' => ['integer'],
        'mr' => ['integer'],
    ];

    protected $fillable = [
        'nft_id',
        'initial_object_id',
        'type',
        'subtype',
        'active',
        'name',
        'short_description',
        'description',
        'portrait_image',
        'featured_image',
        'tpd_entry_url',
        'hp',
        'ad',
        'ap',
        'def',
        'mr'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
    ];

    // RELATIONS

    public function nft()
    {
        return $this->belongsTo('App\Modules\Blockchain\Block\Domain\Nft', 'nft_id');
    }

    public function initial_object()
    {
        return $this->belongsTo('App\Modules\Game\ThePhoenixDiary\Domain\TpdObject', 'initial_object_id');
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
