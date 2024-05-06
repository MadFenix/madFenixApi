<?php
namespace App\Modules\Habit\Domain;

use App\Modules\Base\Domain\BaseDomain;

class HabitComplete extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'habit_id' => ['required', 'integer', 'exists:habits,id'],
    ];

    protected $fillable = [
        'habit_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    // RELATIONS

    public function habit()
    {
        return $this->belongsTo('App\Modules\Habit\Domain\Habit', 'habit_id');
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
