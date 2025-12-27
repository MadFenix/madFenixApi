<?php
namespace App\Modules\Universe\Character\Domain;

use App\Modules\Base\Domain\BaseDomain;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CharacterMedia extends BaseDomain
{
    use HasUuids;

    protected $table = 'chr_media_character';

    protected $fillable = ['character_id', 'media_type', 'url', 'alt_text', 'sort_order'];

    public function character()
    {
        return $this->belongsTo(Character::class, 'character_id');
    }

    public function getIcon(): string
    {
        return 'image';
    }

    public function remove(): bool
    {
        return $this->delete();
    }

    public function getValidationContext(): array
    {
        return [
            'character_id' => ['required', 'uuid', 'exists:chr_characters,id'],
            'media_type' => ['required', 'in:portrait,cover,tarot_front,tarot_back,other'],
            'url' => ['required', 'string'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['integer'],
        ];
    }
}
