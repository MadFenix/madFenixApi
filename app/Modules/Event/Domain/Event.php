<?php
namespace App\Modules\Event\Domain;

use App\Modules\Base\Domain\BaseDomain;
use App\Modules\Base\Traits\Descriptive;

class Event extends BaseDomain
{
    use Descriptive;

    const VALIDATION_COTNEXT = [
        'creator_id' => ['required', 'integer', 'exists:users,id'],
        'destinator_id' => ['required', 'integer', 'exists:users,id'],
        'description' => ['required', 'string', 'min:4', 'max:255'],
        'details' => ['required', 'string', 'min:8', 'max:2000'],
        'product_gift_delivered' => ['integer'],
        'product_gift_id' => ['integer'],
    ];

    protected $fillable = ['description', 'details', 'creator_id', 'destinator_id', 'start_at', 'start_at', 'end_at', 'read_at', 'product_gift_delivered', 'product_gift_id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    // RELATIONS

    public function creator()
    {
        return $this->belongsTo('App\Modules\User\Domain\User', 'creator_id');
    }

    public function destinator()
    {
        return $this->belongsTo('App\Modules\User\Domain\User', 'destinator_id');
    }

    public function product_gift()
    {
        return $this->belongsTo('App\Modules\Store\Domain\Product', 'product_gift_id');
    }

    // GETTERS

    public function getValidationContext(): array
    {
        return self::VALIDATION_COTNEXT;
    }

    public function getIcon(): string
    {
        return 'calendar-day';
    }

    // Others

    public function remove(): bool
    {
        return $this->delete();
    }
}
