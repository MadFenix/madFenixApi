<?php
namespace App\Modules\Blockchain\Wallet\Domain;

use App\Modules\Base\Domain\BaseDomain;

class Wallet extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'user_id' => ['required', 'integer', 'exists:users,id'],
        'balance' => ['required', 'regex:/^(?:d*.d{1,2}|d+)$/','min:0'],
    ];

    protected $fillable = [
        'user_id',
        'balance',
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
        return 'wallet';
    }

    // Others

    public function remove(): bool
    {
        return $this->delete();
    }
}
