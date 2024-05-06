<?php


namespace App\Modules\Game\Profile\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Blockchain\Block\Domain\BlockchainHistorical;
use App\Modules\Blockchain\Block\Domain\Nft;
use App\Modules\Blockchain\Block\Domain\NftIdentification;
use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\Game\Profile\Transformers\Profile as ProfileTransformer;
use App\Modules\Habit\Domain\Habit;
use App\Modules\User\Domain\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\Profile';
    }

    public function subtractPlumaUser()
    {
        /** @var User $user */
        $user = auth()->user();

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }

        $profile->plumas--;
        $profileSaved = $profile->save();

        $newBlockchainHistorical = new BlockchainHistorical();
        $newBlockchainHistorical->user_id = $user->id;
        $newBlockchainHistorical->plumas = -1;
        $newBlockchainHistorical->memo = "Used";
        $blockchainHistoricalSaved = $newBlockchainHistorical->save();

        return $profileSaved && $blockchainHistoricalSaved
            ? response()->json('Se ha restado la pluma del usuario.')
            : response()->json('Error al guardar el perfil.', 500);
    }

    public function subtractOroUser()
    {
        /** @var User $user */
        $user = auth()->user();

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }

        $profile->oro--;
        $profileSaved = $profile->save();

        $newBlockchainHistorical = new BlockchainHistorical();
        $newBlockchainHistorical->user_id = $user->id;
        $newBlockchainHistorical->piezas_de_oro_ft = -1;
        $newBlockchainHistorical->memo = "Used";
        $blockchainHistoricalSaved = $newBlockchainHistorical->save();

        return $profileSaved && $blockchainHistoricalSaved
            ? response()->json('Se ha restado el oro del usuario.')
            : response()->json('Error al guardar el perfil.', 500);
    }

    public function getUserProfile()
    {
        /** @var User $user */
        $user = auth()->user();

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }

        $nftIdentifications = NftIdentification::where('user_id', '=', $user->id)->get();

        $userHabits = Habit::where('user_id', '=', $user->id)->orderBy('order')->get();
        $userHabitIds = [];
        foreach ($userHabits as $userHabit) {
            $userHabitIds[] = $userHabit->id;
        }
        $dateNow = Carbon::now();
        $dateNow->startOfDay();
        $userHabitCompletes = Habit::where('created_at', '<', $dateNow->format('Y-m-d H:i:s'))->whereIn('tournament_id', $userHabitIds)->get();
        $userHabitCompletedIds = [];
        foreach ($userHabitCompletes as $userHabitComplete) {
            $userHabitCompletedIds[] = $userHabitComplete->habit_id;
        }

        $returnProfile = new \stdClass();
        $returnProfile->username = $user->name;
        $returnProfile->email = $user->email;
        $returnProfile->description = $profile->description;
        $returnProfile->details = $profile->details;
        $returnProfile->avatar = $profile->avatar;
        $returnProfile->plumas = $profile->plumas;
        $returnProfile->oro = $profile->oro;
        $returnProfile->user_twitch = $profile->twitch_user_name;
        $returnProfile->user_steam = $profile->steam_user_name;
        $returnProfile->referred_code = $profile->referred_code;
        $returnProfile->referred_code_from = $profile->referred_code_from;
        $returnProfile->nfts = [];
        foreach ($nftIdentifications as $nftIdentification) {
            $nft = Nft::find($nftIdentification->nft_id);
            $newNft = (object) $nftIdentification->toArray();
            $newNft->nft = (object) $nft->toArray();
            $returnProfile->nfts[] = $newNft;
        }
        $returnProfile->habits = [];
        foreach ($userHabits as $userHabit) {
            $newHabit = (object) $userHabit->toArray();
            $newHabit->habit_completed = in_array($userHabit->id, $userHabitCompletedIds);
            $returnProfile->habits[] = $newHabit;
        }


        return response()->json($returnProfile);
    }

    public function setUserProfileReferredCode(Request $request)
    {
        $data = $request->validate(['referred_code' => 'required|string']);

        /** @var User $user */
        $user = auth()->user();

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }

        $profileWithSameReferredCode = Profile::where('referred_code', '=', $data['referred_code'])->first();
        if ($profileWithSameReferredCode) {
            return response()->json('Ya existe un usuario con este código de referido.', 400);
        }

        $profile->referred_code = $data['referred_code'];
        $profileSaved = $profile->save();

        return $profileSaved
            ? response()->json('Se ha establecido tu código de referido.')
            : response()->json('Error al guardar el perfil.', 500);
    }

    public function setUserProfileReferredCodeFrom(Request $request)
    {
        $data = $request->validate(['referred_code_from' => 'required|string']);

        /** @var User $user */
        $user = auth()->user();

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }
        if ($profile->referred_code_from) {
            return response()->json('Ya tienes un código de referido.', 400);
        }

        $profileWithSameReferredCode = Profile::where('referred_code', '=', $data['referred_code_from'])->first();
        if (!$profileWithSameReferredCode) {
            return response()->json('No existe ningún usuario con este código de referido.', 404);
        }

        $oroToReferred = 5;

        $profile->referred_code_from = $data['referred_code_from'];
        $profile->oro += $oroToReferred;
        $profileSaved = $profile->save();

        $newBlockchainHistorical = new BlockchainHistorical();
        $newBlockchainHistorical->user_id = $profile->user_id;
        $newBlockchainHistorical->piezas_de_oro_ft = $oroToReferred;
        $newBlockchainHistorical->memo = "Attach referred code from. User " . $profileWithSameReferredCode->user_id;
        $blockchainHistoricalSaved = $newBlockchainHistorical->save();

        $profileWithSameReferredCode->oro += $oroToReferred;
        $profileSaved2 = $profileWithSameReferredCode->save();

        $newBlockchainHistorical2 = new BlockchainHistorical();
        $newBlockchainHistorical2->user_id = $profileWithSameReferredCode->user_id;
        $newBlockchainHistorical2->piezas_de_oro_ft = $oroToReferred;
        $newBlockchainHistorical2->memo = "New referred. User " . $profile->user_id;
        $blockchainHistoricalSaved2 = $newBlockchainHistorical2->save();

        return $profileSaved && $profileSaved2 && $blockchainHistoricalSaved && $blockchainHistoricalSaved2
            ? response()->json('Se ha establecido el código del usuario referido en tu perfil.')
            : response()->json('Error al guardar el perfil.', 500);
    }

    public function addPluma(Request $request)
    {
        $data = $request->validate(['user_id' => 'required|integer', 'plumas' => 'required|integer']);
        /** @var User $user */
        $user = auth()->user();

        if ($user->email != 'iam@valentigamez.com') {
            return response()->json('Solo el administrador puede ejecutar esta función.', 403);
        }

        $profile = Profile::where('user_id', '=', $data['user_id'])->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }

        $profile->plumas += $data['plumas'];
        $profileSaved = $profile->save();

        $newBlockchainHistorical = new BlockchainHistorical();
        $newBlockchainHistorical->user_id = $data['user_id'];
        $newBlockchainHistorical->plumas = $data['plumas'];
        $newBlockchainHistorical->memo = "Admin decision";
        $blockchainHistoricalSaved = $newBlockchainHistorical->save();

        return $profileSaved && $blockchainHistoricalSaved
            ? response()->json('Se han sumado las plumas al usuario.')
            : response()->json('Error al guardar el perfil.', 500);
    }

    public function addOro(Request $request)
    {
        $data = $request->validate(['user_id' => 'required|integer', 'oro' => 'required|integer']);
        /** @var User $user */
        $user = auth()->user();

        if ($user->email != 'iam@valentigamez.com') {
            return response()->json('Solo el administrador puede ejecutar esta función.', 403);
        }

        $profile = Profile::where('user_id', '=', $data['user_id'])->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }

        $profile->oro += $data['oro'];
        $profileSaved = $profile->save();

        $newBlockchainHistorical = new BlockchainHistorical();
        $newBlockchainHistorical->user_id = $data['user_id'];
        $newBlockchainHistorical->piezas_de_oro_ft = $data['piezas_de_oro_ft'];
        $newBlockchainHistorical->memo = "Admin decision";
        $blockchainHistoricalSaved = $newBlockchainHistorical->save();

        return $profileSaved && $blockchainHistoricalSaved
            ? response()->json('Se ha sumado el oro al usuario.')
            : response()->json('Error al guardar el perfil.', 500);
    }

}
