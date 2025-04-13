<?php
namespace App\Modules\Game\ThePhoenixDiary\Domain;

use App\Modules\Base\Domain\BaseDomain;

class TpdObject extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'nft_id' => ['integer'],
        'character_id' => ['integer'],
        'type' => ['string'],
        'subtype' => ['string'],
        'active' => ['integer'],
        'active_in_game_store' => ['integer'],
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
        'act' => ['integer'],
        'actions' => ['string'],
        'answer_nft_common' => ['string'],
        'answer_nft_common_action' => ['string'],
        'answer_nft_uncommon' => ['string'],
        'answer_nft_uncommon_action' => ['string'],
        'answer_nft_rare' => ['string'],
        'answer_nft_rare_action' => ['string'],
        'answer_nft_legendary' => ['string'],
        'answer_nft_legendary_action' => ['string'],
        'answer_common' => ['string'],
        'answer_common_action' => ['string'],
        'answer_uncommon' => ['string'],
        'answer_uncommon_action' => ['string'],
        'answer_rare' => ['string'],
        'answer_rare_action' => ['string'],
        'answer_legendary' => ['string'],
        'answer_legendary_action' => ['string'],
    ];

    protected $fillable = [
        'nft_id',
        'character_id',
        'type',
        'subtype',
        'active',
        'active_in_game_store',
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
        'mr',
        'act',
        'actions',
        'answer_nft_common',
        'answer_nft_common_action',
        'answer_nft_uncommon',
        'answer_nft_uncommon_action',
        'answer_nft_rare',
        'answer_nft_rare_action',
        'answer_nft_legendary',
        'answer_nft_legendary_action',
        'answer_common',
        'answer_common_action',
        'answer_uncommon',
        'answer_uncommon_action',
        'answer_rare',
        'answer_rare_action',
        'answer_legendary',
        'answer_legendary_action'
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

    public function character()
    {
        return $this->belongsTo('App\Modules\Game\ThePhoenixDiary\Domain\TpdCharacter', 'character_id');
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
