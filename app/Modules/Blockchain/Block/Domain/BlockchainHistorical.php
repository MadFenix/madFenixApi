<?php
namespace App\Modules\Blockchain\Block\Domain;

use App\Modules\Base\Domain\BaseDomain;

class BlockchainHistorical extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'user_id' => ['required', 'integer', 'exists:users,id'],
        'plumas' => ['nullable', 'integer'],
        'piezas_de_oro_ft' => ['nullable', 'integer'],
        'piezas_de_oro_nft' => ['nullable', 'string'],
        'dragones_custodio' => ['nullable', 'string'],
        'memo' => ['nullable', 'string'],
    ];

    protected $fillable = [
        'user_id',
        'plumas',
        'piezas_de_oro_ft',
        'piezas_de_oro_nft',
        'dragones_custodio',
        'memo',
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
