<?php
namespace App\Modules\Game\Fighter\Domain;

use App\Modules\Base\Domain\BaseDomain;

class FighterUser extends BaseDomain
{
    const VALIDATION_COTNEXT = [
        'user_id' => ['required', 'integer', 'exists:users,id'],
        'avatar_image' => ['string'],
        'avatar_frame' => ['string'],
        'action_frame' => ['string'],
        'card_frame' => ['string'],
        'game_arena' => ['string'],
        'cups' => ['integer'],
        'rank' => ['string'],
        'decks_available' => ['integer'],
        'deck_current' => ['integer'],
        'deck_1' => ['string'],
        'deck_2' => ['string'],
        'deck_3' => ['string'],
        'deck_4' => ['string'],
        'deck_5' => ['string'],
        'deck_6' => ['string'],
        'deck_7' => ['string'],
        'deck_8' => ['string'],
        'deck_9' => ['string'],
        'deck_10' => ['string'],
        'ready_to_play' => ['boolean'],
        'ready_to_play_last' => ['date'],
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
        'avatar_image',
        'avatar_frame',
        'action_frame',
        'card_frame',
        'game_arena',
        'cups',
        'decks_available',
        'deck_current',
        'deck_1',
        'deck_2',
        'deck_3',
        'deck_4',
        'deck_5',
        'deck_6',
        'deck_7',
        'deck_8',
        'deck_9',
        'deck_10',
        'ready_to_play',
        'ready_to_play_last',
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
    protected $casts = [
        'ready_to_play_last' => 'datetime',
    ];

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
