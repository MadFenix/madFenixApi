<?php
namespace App\Modules\Universe\Character\Domain;

use App\Modules\Base\Domain\BaseDomain;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SubcategoryDefinition extends BaseDomain
{
    use HasUuids;

    protected $table = 'chr_subcategory_definitions';

    protected $fillable = ['subcategory_id', 'value'];

    public function subcategory()
    {
        return $this->belongsTo(SubcategoryType::class, 'subcategory_id');
    }

    public function getIcon(): string
    {
        return 'list';
    }

    public function remove(): bool
    {
        return $this->delete();
    }

    public function getValidationContext(): array
    {
        return [
            'subcategory_id' => ['required', 'uuid', 'exists:chr_subcategory_types,id'],
            'value' => ['required', 'string', 'max:128'],
        ];
    }
}
