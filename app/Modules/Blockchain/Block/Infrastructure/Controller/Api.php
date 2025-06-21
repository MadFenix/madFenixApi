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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Agustind\Ethsignature;
use App\Modules\Blockchain\Block\Infrastructure\Service\Polygon;

/**
 * @group Blockchain operations
 *
 * APIs for managing blockchain transfers to Hedera
 */
class Api
{
    /**
     * Transfer Plumas to Hedera
     *
     * Transfer a specified amount of Plumas (feathers) from the user's account to Hedera blockchain.
     *
     * @param  \Illuminate\Http\Request  $request
     * @bodyParam id_hedera string required The Hedera account ID to transfer to. Example: "0.0.123456"
     * @bodyParam plumas integer required The amount of plumas to transfer (minimum 1). Example: 10
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
     * Transfer Oro to Hedera
     *
     * Transfer a specified amount of Oro (gold) from the user's account to Hedera blockchain.
     *
     * @param  \Illuminate\Http\Request  $request
     * @bodyParam id_hedera string required The Hedera account ID to transfer to. Example: "0.0.123456"
     * @bodyParam oro integer required The amount of oro to transfer (minimum 1). Example: 5
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
     * Transfer NFT to Hedera
     *
     * Transfer a specific NFT from the user's account to Hedera blockchain.
     *
     * @param  \Illuminate\Http\Request  $request
     * @bodyParam id_hedera string required The Hedera account ID to transfer to. Example: "0.0.123456"
     * @bodyParam nft_identification_id integer required The ID of the NFT identification to transfer. Example: 42
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

    /**
     * Generate a message that the user must sign to prove wallet ownership.
     */
    public function getSignMessage(Request $request): JsonResponse
    {
        $request->validate(['address' => 'required|string']);
        $address = strtolower($request->address);
        $message = Str::random(40);
        Cache::put('polygon_sign_' . $address, $message, now()->addMinutes(10));

        return response()->json(['message' => $message]);
    }

    /**
     * Verify the signed message and return owned token ids for a collection.
     */
    public function getOwnedTokens(Request $request): JsonResponse
    {
        $data = $request->validate([
            'address' => 'required|string',
            'signature' => 'required|string',
            'contract_address' => 'required|string',
        ]);

        $address = strtolower($data['address']);
        $message = Cache::pull('polygon_sign_' . $address);
        if (!$message) {
            return response()->json('Sign message not found or expired', 400);
        }

        $signature = new Ethsignature();
        if (!$signature->verify($message, $data['signature'], $data['address'])) {
            return response()->json('Invalid signature', 400);
        }

        $polygon = new Polygon();
        $tokenIds = $polygon->getOwnedTokenIds($address, strtolower($data['contract_address']));

        return response()->json($tokenIds);
    }
}
