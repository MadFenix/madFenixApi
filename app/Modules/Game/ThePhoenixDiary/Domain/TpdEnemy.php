<?php
namespace App\Modules\Game\ThePhoenixDiary\Domain;

use App\Modules\Base\Domain\BaseDomain;

class TpdEnemy extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'nft_id' => ['integer'],
        'tpd_enemy_id' => ['integer'],
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
        'act' => ['integer'],
        'actions' => ['string'],
        'special_reward' => ['string'],
        'answer_nft_common' => ['string'],
        'answer_nft_common_action' => ['string'],
        'answer_nft_uncommon' => ['string'],
        'answer_nft_uncommon_action' => ['string'],
        'answer_nft_rare' => ['string'],
        'answer_nft_rare_action' => ['string'],
        'answer_nft_legendary' => ['string'],
    ];

    protected $fillable = [
        'nft_id',
        'tpd_enemy_id',
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
        'mr',
        'act',
        'actions',
        'special_reward',
        'answer_nft_common',
        'answer_nft_common_action',
        'answer_nft_uncommon',
        'answer_nft_uncommon_action',
        'answer_nft_rare',
        'answer_nft_rare_action',
        'answer_nft_legendary'
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

    public function tpd_enemy()
    {
        return $this->belongsTo('App\Modules\Game\ThePhoenixDiary\Domain\TpdEnemy', 'tpd_enemy_id');
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
