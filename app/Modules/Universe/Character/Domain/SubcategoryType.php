<?php
namespace App\Modules\Universe\Character\Domain;

use App\Modules\Base\Domain\BaseDomain;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SubcategoryType extends BaseDomain
{
    use HasUuids;

    protected $table = 'chr_subcategory_types';

    protected $fillable = ['category_id', 'name'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function getIcon(): string
    {
        return 'layer-group';
    }

    public function remove(): bool
    {
        return $this->delete();
    }

    public function getValidationContext(): array
    {
        return [
            'category_id' => ['required', 'uuid', 'exists:chr_categories,id'],
            'name' => ['required', 'string', 'max:64'],
        ];
    }
}
