<?php
namespace App\Modules\Universe\Character\Domain;

use App\Modules\Base\Domain\BaseDomain;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Role extends BaseDomain
{
    use HasUuids;

    protected $table = 'chr_roles';

    protected $fillable = ['name'];

    public function getIcon(): string
    {
        return 'user-tag';
    }

    public function remove(): bool
    {
        return $this->delete();
    }

    public function getValidationContext(): array
    {
        return [
            'name' => ['required', 'string', 'max:64', 'unique:chr_roles'],
        ];
    }
}
