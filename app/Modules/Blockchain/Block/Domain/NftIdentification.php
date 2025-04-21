<?php
namespace App\Modules\Blockchain\Block\Domain;

use App\Modules\Base\Domain\BaseDomain;

class NftIdentification extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'name' => ['required', 'string'],
        'description' => ['nullable', 'string'],
        'image' => ['nullable', 'string'],
        'nft_identification' => ['required', 'integer'],
        'nft_id' => ['required', 'integer', 'exists:nfts,id'],
        'rarity' => ['nullable', 'string'],
        'tag_1' => ['nullable', 'string'],
        'tag_2' => ['nullable', 'string'],
        'tag_3' => ['nullable', 'string'],
        'madfenix_ownership' => ['nullable'],
        'user_id' => ['nullable', 'integer', 'exists:users,id'],
        'user_id_hedera' => ['nullable', 'integer', 'exists:users,id'],
    ];

    protected $fillable = [
        'name',
        'description',
        'image',
        'nft_identification',
        'nft_id',
        'rarity',
        'tag_1',
        'tag_2',
        'tag_3',
        'madfenix_ownership',
        'user_id',
        'user_id_hedera',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    // RELATIONS

    public function user()
    {
        return $this->belongsTo('App\Modules\User\Domain\User', 'user_id');
    }

    public function user_hedera()
    {
        return $this->belongsTo('App\Modules\User\Domain\User', 'user_id_hedera');
    }

    public function nft()
    {
        return $this->belongsTo('App\Modules\Blockchain\Block\Domain\Nft', 'nft_id');
    }

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
