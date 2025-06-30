<?php

namespace App\Modules\Page\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Page\Domain\Page as PageModel;

class Page extends BaseTransformer
{
    /**
     * The resource instance.
     *
     * @var mixed|PageModel
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge(parent::toArray($request),[
            'name' => $this->name,
            'content' => $this->content,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'seo_image' => $this->seo_image,
        ]);
    }
}
