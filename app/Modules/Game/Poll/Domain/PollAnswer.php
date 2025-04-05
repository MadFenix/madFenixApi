<?php
namespace App\Modules\Game\Poll\Domain;

use App\Modules\Base\Domain\BaseDomain;

class PollAnswer extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'poll_id' => ['nullable', 'integer', 'exists:polls,id'],
        'user_id' => ['required', 'integer', 'exists:users,id'],
        'plumas' => ['required', 'integer'],
        'cronistas' => ['required', 'integer'],
        'answer' => ['string'],
    ];

    protected $fillable = [
        'poll_id',
        'user_id',
        'plumas',
        'cronistas',
        'answer',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    // RELATIONS

    public function poll()
    {
        return $this->belongsTo('App\Modules\Game\Poll\Domain\Poll', 'poll_id');
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
