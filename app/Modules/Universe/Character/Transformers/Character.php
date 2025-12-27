<?php

namespace App\Modules\Universe\Character\Transformers;

use App\Modules\Base\Transformers\BaseTransformer;
use App\Modules\Universe\Character\Domain\Character as CharacterModel;

class Character extends BaseTransformer
{
    /**
     * The resource instance.
     *
     * @var mixed|CharacterModel
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
        return array_merge(parent::toArray($request), [
            'name' => $this->name,
            'original_universe_id' => $this->original_universe_id,
            'category_id' => $this->category_id,
            'subcategory_type_id' => $this->subcategory_type_id,
            'subcategory_definition_id' => $this->subcategory_definition_id,
            'is_undead' => $this->is_undead,
            'undead_suffix_id' => $this->undead_suffix_id,
            'clone_prefix_id' => $this->clone_prefix_id,
            'role_id' => $this->role_id,
            'hierarchy_id' => $this->hierarchy_id,
            'rank_id' => $this->rank_id,
            'archetype_id' => $this->archetype_id,
            'caste' => $this->caste,
            'gender' => $this->gender,
            'age_text' => $this->age_text,
            'age_years' => $this->age_years,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'large_description' => $this->large_description,
            'personality' => $this->personality,
            'backstory' => $this->backstory,
            'cone_notes' => $this->cone_notes,
            // Relaciones (opcionalmente cargadas)
            'category' => $this->whenLoaded('category'),
            'subcategory_type' => $this->whenLoaded('subcategoryType'),
            'subcategory_definition' => $this->whenLoaded('subcategoryDefinition'),
            'role' => $this->whenLoaded('role'),
            'hierarchy' => $this->whenLoaded('hierarchy'),
            'rank' => $this->whenLoaded('rank'),
            'archetype' => $this->whenLoaded('archetype'),
            'stats' => $this->whenLoaded('stats'),
            'media' => $this->whenLoaded('media'),
            'events' => $this->whenLoaded('events'),
            'expressions' => $this->whenLoaded('expressions'),
            'abilities' => $this->whenLoaded('abilities'),
            'category_fields' => $this->whenLoaded('categoryFields'),
        ]);
    }
}
