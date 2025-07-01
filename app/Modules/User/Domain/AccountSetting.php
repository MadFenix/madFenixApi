<?php
namespace App\Modules\User\Domain;

use App\Modules\Base\Domain\BaseDomain;

class AccountSetting extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'page_builder_sessions_used' => ['required', 'integer'],
        'page_builder_sessions_total' => ['required', 'integer'],
    ];

    protected $fillable = [
        'page_builder_sessions_used',
        'page_builder_sessions_total'
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
        return 'user';
    }

    // Others

    public function remove(): bool
    {
        return $this->delete();
    }
}
