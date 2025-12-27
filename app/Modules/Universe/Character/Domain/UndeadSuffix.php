<?php
namespace App\Modules\Universe\Character\Domain;

use App\Modules\Base\Domain\BaseDomain;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UndeadSuffix extends BaseDomain
{
    use HasUuids;

    protected $table = 'chr_category_undead_suf';

    protected $fillable = ['value'];

    public function getIcon(): string
    {
        return 'ghost';
    }

    public function remove(): bool
    {
        return $this->delete();
    }

    public function getValidationContext(): array
    {
        return [
            'value' => ['required', 'string', 'max:64', 'unique:chr_category_undead_suf'],
        ];
    }
}
