<?php
namespace App\Modules\Assistant\OpenAI\Domain;

use Illuminate\Database\Eloquent\Model;

class WelcomeMessage extends Model
{
    const VALIDATION_COTNEXT = [
        'assistance_id' => ['required', 'integer', 'exists:assistances,id'],
        'message_id' => ['required', 'integer'],
        'response' => ['required', 'string', 'min:2'],
    ];

    protected $fillable = [
        'assistance_id',
        'message_id',
        'response',
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
