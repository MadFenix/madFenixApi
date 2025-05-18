<?php

namespace App\Modules\Event\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Event\Domain\EventMeta as EventMetaModel;
use App\Modules\User\Transformers\UserSummary;

class EventMeta extends BaseTransformer
{

    /**
     * The resource instance.
     *
     * @var mixed|EventMetaModel
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
            'details' => $this->details,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'product_gift_id' => $this->product_gift_id,
            'creator_id' => $this->creator_id,
        ]);
    }
}
