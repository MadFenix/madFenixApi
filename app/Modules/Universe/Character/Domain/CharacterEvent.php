<?php
namespace App\Modules\Universe\Character\Domain;

use App\Modules\Base\Domain\BaseDomain;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CharacterEvent extends BaseDomain
{
    use HasUuids;

    protected $table = 'chr_evt_character_event';

    protected $fillable = ['character_id', 'event_id', 'title', 'description', 'happened_at', 'sort_order'];

    protected $casts = [
        'happened_at' => 'date',
    ];

    public function character()
    {
        return $this->belongsTo(Character::class, 'character_id');
    }

    public function event()
    {
        return $this->belongsTo('App\Modules\Event\Domain\Event', 'event_id');
    }

    public function getIcon(): string
    {
        return 'calendar-check';
    }

    public function remove(): bool
    {
        return $this->delete();
    }

    public function getValidationContext(): array
    {
        return [
            'character_id' => ['required', 'uuid', 'exists:chr_characters,id'],
            'event_id' => ['nullable', 'uuid', 'exists:evt_events,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'happened_at' => ['nullable', 'date'],
            'sort_order' => ['integer'],
        ];
    }
}
