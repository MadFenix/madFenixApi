<?php


namespace App\Modules\Game\Profile\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\User\Domain\User;
use Illuminate\Http\Request;

class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\Profile';
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

        return $profileSaved
            ? response()->json('Se han sumado las plumas al usuario.')
            : response()->json('Error al guardar el perfil.', 500);
    }

}
