<?php
namespace App\Modules\Universe\Character\Domain;

use App\Modules\Base\Domain\BaseDomain;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Ability extends BaseDomain
{
    use HasUuids;

    protected $table = 'chr_abilities';

    protected $fillable = ['character_id', 'name', 'affinity_base', 'aptitude_type', 'cone_hint', 'notes'];

    public function character()
    {
        return $this->belongsTo(Character::class, 'character_id');
    }

    public function getIcon(): string
    {
        return 'fist-raised';
    }

    public function remove(): bool
    {
        return $this->delete();
    }

    public function getValidationContext(): array
    {
        return [
            'character_id' => ['required', 'uuid', 'exists:chr_characters,id'],
            'name' => ['required', 'string', 'max:255'],
            'affinity_base' => ['required', 'integer'],
            'aptitude_type' => ['required', 'in:Fisica,Magica,Cuantica'],
            'cone_hint' => ['nullable', 'in:Nucleo,ZonaCercana,ZonaMedia,Periferia'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
