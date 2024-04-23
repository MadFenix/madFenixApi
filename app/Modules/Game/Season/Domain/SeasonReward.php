<?php
namespace App\Modules\Game\Season\Domain;

use App\Modules\Base\Domain\BaseDomain;

class SeasonReward extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'level' => ['required', 'integer'],
        'required_points' => ['required', 'integer'],
        'oro' => ['nullable', 'integer'],
        'plumas' => ['nullable', 'integer'],
        'nft_id' => ['nullable', 'integer', 'exists:nfts,id'],
        'max_nft_rewards' => ['nullable', 'integer'],
        'custom_reward' => ['nullable', 'string'],
        'season_id' => ['required', 'integer', 'exists:seasons,id'],
    ];

    protected $fillable = [
        'level',
        'required_points',
        'oro',
        'plumas',
        'nft_id',
        'max_nft_rewards',
        'custom_reward',
        'season_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    // RELATIONS

    public function nft()
    {
        return $this->belongsTo('App\Modules\Blockchain\Block\Domain\Nft', 'nft_id');
    }

    public function season()
    {
        return $this->belongsTo('App\Modules\Game\Season\Domain\Season', 'season_id');
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
