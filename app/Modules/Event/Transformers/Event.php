<?php

namespace App\Modules\Event\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Event\Domain\Event as EventModel;
use App\Modules\User\Transformers\UserSummary;

class Event extends BaseTransformer
{

    /**
     * The resource instance.
     *
     * @var mixed|EventModel
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
        return [
            'id' => $this->id,
            'icon' => $this->getIcon(),
            'description' => $this->description,
            'short-description' => $this->shortReadable,
            'long-description' => $this->longReadable,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'details' => $this->details,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'read_at' => (empty($this->read_at))? null : $this->read_at,
            'product_gift_delivered' => (empty($this->product_gift_delivered))? null : $this->product_gift_delivered,
            'product_gift_id' => $this->product_gift_id,
            'creator' => new UserSummary($this->creator),
            'destinator' => (empty($this->destinator))? null : new UserSummary($this->destinator),
        ];
    }
}
