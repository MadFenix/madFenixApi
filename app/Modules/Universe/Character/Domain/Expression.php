<?php
namespace App\Modules\Universe\Character\Domain;

use App\Modules\Base\Domain\BaseDomain;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Expression extends BaseDomain
{
    use HasUuids;

    protected $table = 'chr_expressions';

    protected $fillable = ['character_id', 'expression', 'kind'];

    public function character()
    {
        return $this->belongsTo(Character::class, 'character_id');
    }

    public function getIcon(): string
    {
        return 'comment-dots';
    }

    public function remove(): bool
    {
        return $this->delete();
    }

    public function getValidationContext(): array
    {
        return [
            'character_id' => ['required', 'uuid', 'exists:chr_characters,id'],
            'expression' => ['required', 'string'],
            'kind' => ['nullable', 'in:frase,gesto,silencio,tic,otro'],
        ];
    }
}
