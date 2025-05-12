<?php
namespace App\Modules\User\Domain;

use App\Modules\Base\Domain\BaseDomain;

class Identification extends BaseDomain
{
    protected $connection = 'mysql';

    const VALIDATION_COTNEXT = [
        'account' => ['required', 'string'],
        'bbdd_host' => ['required', 'string'],
        'bbdd_name' => ['required', 'string'],
        'bbdd_username' => ['required', 'string'],
        'bbdd_password' => ['required', 'string'],
    ];

    protected $fillable = [
        'account',
        'bbdd_host',
        'bbdd_name',
        'bbdd_username',
        'bbdd_password'
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
