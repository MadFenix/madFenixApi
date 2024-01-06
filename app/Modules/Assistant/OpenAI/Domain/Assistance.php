<?php
namespace App\Modules\Assistant\OpenAI\Domain;

use Illuminate\Database\Eloquent\Model;

class Assistance extends Model
{
    const VALIDATION_COTNEXT = [
        'open_ai_assistant_id' => ['required', 'string', 'min:2'],
        'google_ai_voice_name' => ['required', 'string', 'min:2'],
        'lastResponse' => ['required', 'string', 'min:2'],
        'name' => ['required', 'string', 'min:2', 'max:255'],
        'gender' => ['required', 'string', 'min:2', 'max:255'],
        'responsesToDo' => ['required', 'numeric', 'between:0,255'],
        'instructions' => ['required', 'string', 'min:2'],
    ];

    protected $fillable = [
        'open_ai_assistant_id',
        'google_ai_voice_name',
        'lastResponse',
        'name',
        'gender',
        'responsesToDo',
        'instructions',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

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
