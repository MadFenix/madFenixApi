<?php

namespace App\Modules\Game\Poll\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Game\Poll\Domain\Poll as PollModel;

class Poll extends BaseTransformer
{
    /**
     * The resource instance.
     *
     * @var mixed|PollModel
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
            'user' => new BaseTransformer($this->user),
            'name' => $this->name,
            'portrait_image' => $this->portrait_image,
            'featured_image' => $this->featured_image,
            'answers' => $this->answers,
            'start_date' => $this->start_date->format('Y-m-d H:i:s'),
            'end_date' => $this->end_date->format('Y-m-d H:i:s'),
        ]);
    }
}
