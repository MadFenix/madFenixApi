<?php
namespace App\Modules\Universe\Character\Domain;

use App\Modules\Base\Domain\BaseDomain;

class ActionResult extends BaseDomain
{
    protected $table = 'chr_action_results';
    public $incrementing = false;

    protected $fillable = ['id', 'label'];

    public function getIcon(): string
    {
        return 'dice';
    }

    public function remove(): bool
    {
        return $this->delete();
    }

    public function getValidationContext(): array
    {
        return [
            'id' => ['required', 'integer'],
            'label' => ['required', 'string', 'max:64'],
        ];
    }
}
