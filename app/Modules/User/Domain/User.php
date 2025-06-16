<?php
namespace App\Modules\User\Domain;

use App\Modules\Base\Domain\BaseDomainInterface;
use App\Modules\Base\Traits\Descriptive;
use App\Modules\Base\Traits\DescriptiveInterface;
use App\Notifications\CustomResetPasswordNotification;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements BaseDomainInterface, DescriptiveInterface
{
    use HasApiTokens,Notifiable,Descriptive;

    const VALIDATION_COTNEXT = [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'string', 'min:8'],
        'newsletter' => ['nullable', 'boolean'],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'newsletter', 'eth_wallet', 'ip',
        'two_factor_pre_enabled', 'two_factor_enabled', 'two_factor_secret',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // RELATIONS

    public function ownEvents()
    {
        return $this->hasMany('App\Modules\Event\Domain\Event', 'creator_id');
    }

    public function assignedEvents()
    {
        return $this->hasMany('App\Modules\Event\Domain\Event', 'destinator_id');
    }

    public function ownNodes()
    {
        return $this->hasMany('App\Modules\Game\Node\Domain\Node', 'creator_id');
    }

    public function ownPlanes()
    {
        return $this->hasMany('App\Modules\Game\Plane\Domain\Plane', 'creator_id');
    }

    public function ownProfiles()
    {
        return $this->hasMany('App\Modules\Game\Profile\Domain\Ranking', 'creator_id');
    }

    public function ownWallets()
    {
        return $this->hasMany('App\Modules\Blockchain\Wallet\Domain\Wallet', 'user_id');
    }

    public function ownBlueprints()
    {
        return $this->hasMany('App\Modules\Game\Blueprint\Domain\Blueprint', 'creator_id');
    }

    // GETTERS

    public function getValidationContext(): array
    {
        $rules = self::VALIDATION_COTNEXT;

        // Si el usuario ya existe (tiene ID), modificamos la regla del email
        if ($this->exists) {
            $rules['email'] = [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->id)
            ];
            // Removemos la validación de password para actualizaciones
            unset($rules['password']);
        }

        return $rules;
    }

    public function getIcon(): string
    {
        return 'user';
    }

    public function getReadableAttribute(): string
    {
        return ($this->name) ?? 'Sin descripción';
    }

    // OTHERS

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }

    public function remove(): bool
    {
        return $this->delete();
    }

    public function __toString()
    {
        return $this->readable;
    }
}
