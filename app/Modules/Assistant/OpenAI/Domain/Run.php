<?php
namespace App\Modules\Assistant\OpenAI\Domain;

use Illuminate\Database\Eloquent\Model;

class Run extends Model
{
    const VALIDATION_COTNEXT = [
        'assistance_id' => ['required', 'integer', 'exists:assistances,id'],
        'assistance_to_id' => ['required', 'integer', 'exists:assistances,id'],
        'thred_id' => ['required', 'string', 'min:2'],
        'response' => ['required', 'string', 'min:2'],
        'run' => ['required', 'string', 'min:2', 'max:255'],
    ];

    protected $fillable = [
        'assistance_id',
        'assistance_to_id',
        'thred_id',
        'response',
        'run',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    // RELATIONS

    public function assistance()
    {
        return $this->belongsTo('App\Modules\Assistant\OpenAI\Domain\Assistance', 'assistance_id');
    }

    public function assistanceTo()
    {
        return $this->belongsTo('App\Modules\Assistant\OpenAI\Domain\Assistance', 'assistance_to_id');
    }

    public function getValidationContext(): array
    {
        return self::VALIDATION_COTNEXT;
    }

    public function getIcon(): string
    {
        return 'cube';
    }

    // Others

    public function remove(): bool
    {
        return $this->delete();
    }
}
