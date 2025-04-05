<?php
namespace App\Modules\Game\Poll\Domain;

use App\Modules\Base\Domain\BaseDomain;

class Poll extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'name' => ['required', 'string'],
        'short_description' => ['string'],
        'description' => ['string'],
        'portrait_image' => ['string'],
        'featured_image' => ['string'],
        'answers' => ['string'],
        'start_date' => ['required', 'date'],
        'end_date' => ['required', 'date'],
    ];

    protected $fillable = [
        'name',
        'short_description',
        'description',
        'portrait_image',
        'featured_image',
        'answers',
        'start_date',
        'end_date',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

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
