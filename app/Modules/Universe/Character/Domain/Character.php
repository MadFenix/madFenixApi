<?php
namespace App\Modules\Universe\Character\Domain;

use App\Modules\Base\Domain\BaseDomain;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Character extends BaseDomain
{
    use HasUuids;

    protected $table = 'chr_characters';

    const VALIDATION_CONTEXT = [
        'name' => ['required', 'string', 'max:255'],
        'original_universe_id' => ['required', 'uuid'],
        'category_id' => ['required', 'uuid'],
        'subcategory_type_id' => ['required', 'uuid'],
        'subcategory_definition_id' => ['required', 'uuid'],
        'is_undead' => ['boolean'],
        'undead_suffix_id' => ['nullable', 'uuid'],
        'clone_prefix_id' => ['nullable', 'uuid'],
        'role_id' => ['required', 'uuid'],
        'hierarchy_id' => ['required', 'uuid'],
        'rank_id' => ['required', 'uuid'],
        'archetype_id' => ['required', 'uuid'],
        'caste' => ['required', 'boolean'],
        'gender' => ['required', 'in:Masculino,Femenino,Indeterminado'],
        'age_text' => ['nullable', 'string', 'max:128'],
        'age_years' => ['nullable', 'integer'],
        'short_description' => ['required', 'string'],
        'description' => ['required', 'string'],
        'large_description' => ['required', 'string'],
        'personality' => ['required', 'string'],
        'backstory' => ['required', 'string'],
        'cone_notes' => ['nullable', 'string'],
    ];

    protected $fillable = [
        'name',
        'original_universe_id',
        'category_id',
        'subcategory_type_id',
        'subcategory_definition_id',
        'is_undead',
        'undead_suffix_id',
        'clone_prefix_id',
        'role_id',
        'hierarchy_id',
        'rank_id',
        'archetype_id',
        'caste',
        'gender',
        'age_text',
        'age_years',
        'short_description',
        'description',
        'large_description',
        'personality',
        'backstory',
        'cone_notes',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_undead' => 'boolean',
        'caste' => 'boolean',
        'age_years' => 'integer',
    ];

    // RELATIONS

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subcategoryType()
    {
        return $this->belongsTo(SubcategoryType::class, 'subcategory_type_id');
    }

    public function subcategoryDefinition()
    {
        return $this->belongsTo(SubcategoryDefinition::class, 'subcategory_definition_id');
    }

    public function undeadSuffix()
    {
        return $this->belongsTo(UndeadSuffix::class, 'undead_suffix_id');
    }

    public function clonePrefix()
    {
        return $this->belongsTo(ClonePrefix::class, 'clone_prefix_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function hierarchy()
    {
        return $this->belongsTo(Hierarchy::class, 'hierarchy_id');
    }

    public function rank()
    {
        return $this->belongsTo(Rank::class, 'rank_id');
    }

    public function archetype()
    {
        return $this->belongsTo(Archetype::class, 'archetype_id');
    }

    public function stats()
    {
        return $this->hasOne(Stat::class, 'character_id');
    }

    public function media()
    {
        return $this->hasMany(CharacterMedia::class, 'character_id');
    }

    public function events()
    {
        return $this->hasMany(CharacterEvent::class, 'character_id');
    }

    public function expressions()
    {
        return $this->hasMany(Expression::class, 'character_id');
    }

    public function abilities()
    {
        return $this->hasMany(Ability::class, 'character_id');
    }

    public function categoryFields()
    {
        return $this->hasMany(CategoryField::class, 'character_id');
    }

    // GETTERS

    public function getValidationContext(): array
    {
        return self::VALIDATION_CONTEXT;
    }

    public function getIcon(): string
    {
        return 'user';
    }

    // Others

    public function remove(): bool
    {
        return $this->delete();
    }
}
