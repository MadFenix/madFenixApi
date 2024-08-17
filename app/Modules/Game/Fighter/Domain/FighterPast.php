<?php
namespace App\Modules\Game\Fighter\Domain;

use App\Modules\Base\Domain\BaseDomain;

class FighterPast extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'user_id' => ['required', 'integer', 'exists:users,id'],
        'game_hash' => ['string'],
        'avatar_image' => ['string'],
        'avatar_frame' => ['string'],
        'action_frame' => ['string'],
        'card_frame' => ['string'],
        'game_arena' => ['string'],
        'decks_available' => ['integer'],
        'deck_current' => ['integer'],
        'ready_to_play' => ['boolean'],
        'playing_with_user' => ['integer'],
        'playing_deck' => ['string'],
        'playing_hand' => ['string'],
        'playing_shift' => ['integer'],
        'playing_hp' => ['integer'],
        'playing_pa' => ['integer'],
        'playing_card_left' => ['string'],
        'playing_card_center' => ['string'],
        'playing_card_right' => ['string'],
    ];

    protected $fillable = [
        'game_hash',
        'avatar_image',
        'avatar_frame',
        'action_frame',
        'card_frame',
        'game_arena',
        'decks_available',
        'deck_current',
        'ready_to_play',
        'playing_with_user',
        'playing_deck',
        'playing_hand',
        'playing_shift',
        'playing_hp',
        'playing_pa',
        'playing_card_left',
        'playing_card_center',
        'playing_card_right',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    // RELATIONS

    public function user()
    {
        return $this->belongsTo('App\Modules\User\Domain\User', 'user_id');
    }

    // GETTERS

    public function getValidationContext(): array
    {
        return self::VALIDATION_COTNEXT;
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
