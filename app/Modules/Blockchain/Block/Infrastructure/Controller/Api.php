<?php


namespace App\Modules\Blockchain\Block\Infrastructure\Controller;

use App\Modules\Blockchain\Block\Domain\Block;
use App\Modules\Blockchain\Block\Domain\BlockchainHistorical;
use App\Modules\Blockchain\Block\Domain\HederaQueue;
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

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }
        if ($profile->plumas < $data['plumas']) {
            return response()->json('No tienes suficientes plumas.', 400);
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
