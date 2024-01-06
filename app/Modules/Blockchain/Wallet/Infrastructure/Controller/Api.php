<?php


namespace App\Modules\Blockchain\Wallet\Infrastructure\Controller;

use App\Modules\Blockchain\Block\Domain\Block;
use App\Modules\Blockchain\Block\Service\Blockchain;
use App\Modules\Blockchain\Wallet\Service\Wallet;
use App\Modules\User\Domain\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Api
{

    /**
     * Display user wallet.
     *
     * @return JsonResponse
     */
    public function show()
    {
        $user = auth()->user();

        $wallet = \App\Modules\Blockchain\Wallet\Domain\Wallet::where('user_id', $user->id)->first();
        if (! $wallet) {
            throw new \Exception('Wallet not found.');
        }

        return response()->json(new \App\Modules\Blockchain\Wallet\Transformers\Wallet($wallet));
    }

    /**
     * Transfer zen
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function transferZen(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'user_id_to_transfer'   => 'required|integer|exists:users,id',
            'amount'                => 'required|regex:/^\d+(\.\d{1,2})?$/|min:0',
        ]);

        if ($user->id === $data['user_id_to_transfer']) {
            throw new \Exception('Invalid user to transfer.');
        }

        $userToTransfer = User::findOrFail($data['user_id_to_transfer']);
        $wallet = new Wallet();

        $wallet->storeTransferZenToBd($user, $userToTransfer, $data['amount']);

        $wallet->storeTransferZenToBlockchain($user, $userToTransfer, $data['amount']);

        return response()->json(true);
    }

    /**
     * Transfer zen
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function sorteo()
    {
        $gente = array(
            'Jarto',
            'Dominguero',
            'Talaverinho',
            'Edu',
            'ElEdu',
            'Xunisan',
            'ki',
            'samu',
        );

        $ganador = rand(0, 7);

        return response()->json($gente[$ganador]);
    }
}
