<?php
namespace App\Modules\Event\Domain;

use App\Modules\Base\Domain\BaseDomain;
use App\Modules\Base\Traits\Descriptive;

class EventMeta extends BaseDomain
{
    use Descriptive;

    const VALIDATION_COTNEXT = [
        'creator_id' => ['required', 'integer', 'exists:users,id'],
        'description' => ['required', 'string', 'min:4', 'max:255'],
        'details' => ['required', 'string', 'min:8', 'max:2000'],
        'product_gift_id' => ['integer'],
        'start_date' => ['required', 'date'],
        'end_date' => ['required', 'date'],
    ];

    protected $fillable = ['description', 'details', 'creator_id', 'start_at', 'start_at', 'end_at', 'product_gift_id'];

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
