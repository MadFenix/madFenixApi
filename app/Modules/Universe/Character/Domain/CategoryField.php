<?php
namespace App\Modules\Universe\Character\Domain;

use App\Modules\Base\Domain\BaseDomain;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CategoryField extends BaseDomain
{
    use HasUuids;

    protected $table = 'chr_category_fields';

    protected $fillable = ['character_id', 'key', 'value'];

    public function character()
    {
        return $this->belongsTo(Character::class, 'character_id');
    }

    public function getIcon(): string
    {
        return 'th-list';
    }

    public function remove(): bool
    {
        return $this->delete();
    }

    public function getValidationContext(): array
    {
        return [
            'character_id' => ['required', 'uuid', 'exists:chr_characters,id'],
            'key' => ['required', 'string', 'max:64'],
            'value' => ['required', 'string'],
        ];
    }
}
