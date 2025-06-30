<?php
namespace App\Modules\Page\Domain;

use App\Modules\Base\Domain\BaseDomain;

class Page extends BaseDomain
{
    const VALIDATION_CONTEXT = [
        'builder_id' => ['nullable', 'string'],
        'name' => ['nullable', 'string'],
        'content' => ['nullable', 'string'],
        'seo_title' => ['nullable', 'string'],
        'seo_description' => ['nullable', 'string'],
        'seo_image' => ['nullable', 'string'],
    ];

    protected $fillable = [
        'builder_id',
        'name',
        'content',
        'seo_title',
        'seo_description',
        'seo_image',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    // GETTERS

    public function getValidationContext(): array
    {
        return self::VALIDATION_CONTEXT;
    }

    public function getIcon(): string
    {
        return 'description';
    }

    // Others

    public function remove(): bool
    {
        return $this->delete();
    }
}
