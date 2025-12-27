<?php
namespace App\Modules\Universe\Character\Domain;

use App\Modules\Base\Domain\BaseDomain;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Rank extends BaseDomain
{
    use HasUuids;

    protected $table = 'chr_ranks';

    protected $fillable = ['name'];

    public function getIcon(): string
    {
        return 'medal';
    }

    public function remove(): bool
    {
        return $this->delete();
    }

    public function getValidationContext(): array
    {
        return [
            'name' => ['required', 'string', 'max:64', 'unique:chr_ranks'],
        ];
    }
}
