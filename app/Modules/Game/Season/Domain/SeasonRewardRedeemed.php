<?php
namespace App\Modules\Game\Season\Domain;

use App\Modules\Base\Domain\BaseDomain;

class SeasonRewardRedeemed extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'nft_identification_id' => ['nullable', 'integer', 'exists:nft_identifications,id'],
        'blockchain_historical_id' => ['nullable', 'integer', 'exists:blockchain_historicals,id'],
        'season_reward_id' => ['required', 'integer', 'exists:season_rewards,id'],
        'user_id' => ['required', 'integer', 'exists:users,id'],
    ];

    protected $fillable = [
        'nft_identification_id',
        'blockchain_historical_id',
        'season_reward_id',
        'user_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    // RELATIONS

    public function nft_identification()
    {
        return $this->belongsTo('App\Modules\Blockchain\Block\Domain\NftIdentification', 'nft_identification_id');
    }

    public function blockchain_historical()
    {
        return $this->belongsTo('App\Modules\Blockchain\Block\Domain\BlockchainHistorical', 'blockchain_historical_id');
    }

    public function season_reward()
    {
        return $this->belongsTo('App\Modules\Game\Season\Domain\SeasonReward', 'season_reward_id');
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
