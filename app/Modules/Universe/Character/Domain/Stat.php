<?php
namespace App\Modules\Universe\Character\Domain;

use App\Modules\Base\Domain\BaseDomain;

class Stat extends BaseDomain
{
    protected $table = 'chr_stats';
    protected $primaryKey = 'character_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['character_id', 'hp', 'ad', 'ap', 'def', 'mr', 'stat_cap', 'uses_magic'];

    protected $casts = [
        'uses_magic' => 'boolean',
    ];

    public function character()
    {
        return $this->belongsTo(Character::class, 'character_id');
    }

    public function getIcon(): string
    {
        return 'chart-bar';
    }

    public function remove(): bool
    {
        return $this->delete();
    }

    public function getValidationContext(): array
    {
        return [
            'character_id' => ['required', 'uuid', 'exists:chr_characters,id'],
            'hp' => ['required', 'integer'],
            'ad' => ['required', 'integer'],
            'ap' => ['required', 'integer'],
            'def' => ['required', 'integer'],
            'mr' => ['required', 'integer'],
            'stat_cap' => ['required', 'integer'],
            'uses_magic' => ['boolean'],
        ];
    }
}
