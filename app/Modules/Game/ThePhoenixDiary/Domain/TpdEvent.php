<?php
namespace App\Modules\Game\ThePhoenixDiary\Domain;

use App\Modules\Base\Domain\BaseDomain;

class TpdEvent extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'nft_id' => ['integer'],
        'character_id' => ['integer'],
        'tpd_enemies_id' => ['integer'],
        'type' => ['string'],
        'subtype' => ['string'],
        'active' => ['integer'],
        'name' => ['string'],
        'short_description' => ['string'],
        'description' => ['string'],
        'portrait_image' => ['string'],
        'featured_image' => ['string'],
        'tpd_entry_url' => ['string'],
        'act' => ['integer'],
        'actions' => ['string'],
        'actions_rewards' => ['string'],
        'actions_nft' => ['string'],
        'actions_rewards_nft' => ['string'],
        'answer_nft_common' => ['string'],
        'answer_nft_uncommon' => ['string'],
        'answer_nft_rare' => ['string'],
        'answer_nft_legendary' => ['string'],
    ];

    protected $fillable = [
        'nft_id',
        'character_id',
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
        'act',
        'actions',
        'actions_rewards',
        'actions_nft',
        'actions_rewards_nft',
        'answer_nft_common',
        'answer_nft_uncommon',
        'answer_nft_rare',
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
