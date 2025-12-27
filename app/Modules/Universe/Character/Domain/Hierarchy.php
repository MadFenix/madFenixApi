<?php
namespace App\Modules\Universe\Character\Domain;

use App\Modules\Base\Domain\BaseDomain;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Hierarchy extends BaseDomain
{
    use HasUuids;

    protected $table = 'chr_hierarchies';

    protected $fillable = ['name'];

    public function getIcon(): string
    {
        return 'sitemap';
    }

    public function remove(): bool
    {
        return $this->delete();
    }

    public function getValidationContext(): array
    {
        return [
            'name' => ['required', 'string', 'max:128', 'unique:chr_hierarchies'],
        ];
    }
}
