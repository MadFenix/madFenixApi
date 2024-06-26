<?php


namespace App\Modules\Blockchain\Block\Infrastructure\Controller;

use App\Modules\Blockchain\Block\Domain\Block;
use App\Modules\Blockchain\Block\Domain\BlockchainHistorical;
use App\Modules\Blockchain\Block\Domain\HederaQueue;
use App\Modules\Blockchain\Block\Domain\NftIdentification;
use App\Modules\Blockchain\Block\Service\Blockchain;
use App\Modules\Blockchain\Wallet\Service\Wallet;
use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\User\Domain\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Api
{
    /**
     * Transfer zen
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function transferPlumasToHedera(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'id_hedera'   => 'required',
            'plumas'      => 'required|min:1',
        ]);
        $data['plumas'] = (int) $data['plumas'];

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }
        if ($profile->plumas < $data['plumas']) {
            return response()->json('No tienes suficientes plumas.', 400);
        }
        if ($data['plumas'] < 1) {
            return response()->json('Debes transfereir mínimo 1 pluma.', 400);
        }

        $profile->plumas -= $data['plumas'];
        $profileSaved = $profile->save();
        if (!$profileSaved) {
            return response()->json('No se ha podido guardar el perfil.', 500);
        }

        $newBlockchainHistorical = new BlockchainHistorical();
        $newBlockchainHistorical->user_id = $user->id;
        $newBlockchainHistorical->plumas = -$data['plumas'];
        $blockchainHistoricalSaved = $newBlockchainHistorical->save();
        if (!$blockchainHistoricalSaved) {
            return response()->json('No se ha podido guardar el historico en la blockchain de Mad Fénix.', 500);
        }

        $hederaQueue = new HederaQueue();
        $hederaQueue->user_id = $user->id;
        $hederaQueue->id_hedera = $data['id_hedera'];
        $hederaQueue->plumas = $data['plumas'];
        $hederaQueue->done = false;
        $hederaQueueSaved = $hederaQueue->save();
        if (!$hederaQueueSaved) {
            return response()->json('No se ha podido guardar la cola para enviar a Hedera.', 500);
        }

        $newBlockchainHistorical->memo = "Transferencia a hedera. Queue: " . $hederaQueue->id;
        $blockchainHistoricalSaved = $newBlockchainHistorical->save();
        if (!$blockchainHistoricalSaved) {
            return response()->json('No se ha podido la id de cola en el historico de la blockchain de Mad Fénix.', 500);
        }

        return response()->json(true);
    }

    /**
     * Transfer zen
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function transferOroToHedera(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'id_hedera'   => 'required',
            'oro'      => 'required|min:1',
        ]);
        $data['oro'] = (int) $data['oro'];

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }
        if ($profile->oro < $data['oro']) {
            return response()->json('No tienes suficiente oro.', 400);
        }
        if ($data['oro'] < 1) {
            return response()->json('Debes transfereir mínimo 1 oro.', 400);
        }

        $profile->oro -= $data['oro'];
        $profileSaved = $profile->save();
        if (!$profileSaved) {
            return response()->json('No se ha podido guardar el perfil.', 500);
        }

        $newBlockchainHistorical = new BlockchainHistorical();
        $newBlockchainHistorical->user_id = $user->id;
        $newBlockchainHistorical->piezas_de_oro_ft = -$data['oro'];
        $blockchainHistoricalSaved = $newBlockchainHistorical->save();
        if (!$blockchainHistoricalSaved) {
            return response()->json('No se ha podido guardar el historico en la blockchain de Mad Fénix.', 500);
        }

        $hederaQueue = new HederaQueue();
        $hederaQueue->user_id = $user->id;
        $hederaQueue->id_hedera = $data['id_hedera'];
        $hederaQueue->piezas_de_oro_ft = $data['oro'];
        $hederaQueue->done = false;
        $hederaQueueSaved = $hederaQueue->save();
        if (!$hederaQueueSaved) {
            return response()->json('No se ha podido guardar la cola para enviar a Hedera.', 500);
        }

        $newBlockchainHistorical->memo = "Transferencia a hedera. Queue: " . $hederaQueue->id;
        $blockchainHistoricalSaved = $newBlockchainHistorical->save();
        if (!$blockchainHistoricalSaved) {
            return response()->json('No se ha podido la id de cola en el historico de la blockchain de Mad Fénix.', 500);
        }

        return response()->json(true);
    }

    /**
     * Transfer zen
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function transferNftToHedera(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'id_hedera'                 => 'required',
            'nft_identification_id'     => 'required',
        ]);
        $data['nft_identification_id'] = (int) $data['nft_identification_id'];

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }

        $nft = NftIdentification::where('id', '=', $data['nft_identification_id'])->where('user_id', '=', $user->id)->first();
        if (!$nft) {
            return response()->json('No se ha encontrado el activo digital en tu baúl.', 404);
        }

        $newBlockchainHistorical = new BlockchainHistorical();
        $newBlockchainHistorical->user_id = $user->id;
        $newBlockchainHistorical->nft_identification_id = $data['nft_identification_id'];
        $blockchainHistoricalSaved = $newBlockchainHistorical->save();
        if (!$blockchainHistoricalSaved) {
            return response()->json('No se ha podido guardar el historico en la blockchain de Mad Fénix.', 500);
        }

        $nft->user_id = null;
        $nftSaved = $nft->save();
        if (!$nftSaved) {
            return response()->json('Error al transferir tu nft, contacta con iam@valentigamez.com.', 500);
        }

        $hederaQueue = new HederaQueue();
        $hederaQueue->user_id = $user->id;
        $hederaQueue->id_hedera = $data['id_hedera'];
        $hederaQueue->nft_identification_id = $data['nft_identification_id'];
        $hederaQueue->done = false;
        $hederaQueueSaved = $hederaQueue->save();
        if (!$hederaQueueSaved) {
            return response()->json('No se ha podido guardar la cola para enviar a Hedera.', 500);
        }

        $newBlockchainHistorical->memo = "Transferencia a hedera. Queue: " . $hederaQueue->id;
        $blockchainHistoricalSaved = $newBlockchainHistorical->save();
        if (!$blockchainHistoricalSaved) {
            return response()->json('No se ha podido la id de cola en el historico de la blockchain de Mad Fénix.', 500);
        }

        return response()->json(true);
    }
}
