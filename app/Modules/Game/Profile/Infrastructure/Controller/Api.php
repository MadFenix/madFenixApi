<?php


namespace App\Modules\Game\Profile\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Blockchain\Block\Domain\BlockchainHistorical;
use App\Modules\Blockchain\Block\Domain\Nft;
use App\Modules\Blockchain\Block\Domain\NftIdentification;
use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\Game\Profile\Transformers\Profile as ProfileTransformer;
use App\Modules\User\Domain\User;
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

        $returnProfile = new \stdClass();
        $returnProfile->username = $user->name;
        $returnProfile->email = $user->email;
        $returnProfile->description = $profile->description;
        $returnProfile->details = $profile->details;
        $returnProfile->avatar = $profile->avatar;
        $returnProfile->plumas = $profile->plumas;
        $returnProfile->oro = $profile->oro;
        $returnProfile->nfts = [];
        foreach ($nftIdentifications as $nftIdentification) {
            $nft = Nft::find($nftIdentification->nft_id);
            $newNft = (object) $nftIdentification->toArray();
            $newNft->nft = (object) $nft->toArray();
            $returnProfile->nfts[] = $newNft;
        }


        return response()->json($returnProfile);
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
