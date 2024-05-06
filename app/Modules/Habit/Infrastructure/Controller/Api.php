<?php


namespace App\Modules\Habit\Infrastructure\Controller;

use App\Http\Controllers\Controller;
use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\Habit\Domain\Habit;
use App\Modules\Habit\Domain\HabitComplete;
use App\Modules\Habit\Infrastructure\ApiHelix;
use App\Modules\User\Domain\User;
use Illuminate\Http\Request;

class Api extends Controller
{
    public function postHabit(Request $request) {
        $data = $request->validate(['id' => 'integer', 'name' => 'required|string', 'order' => 'integer']);

        /** @var User $user */
        $user = auth()->user();

        if ($data['id']) {
            $habit = Habit::where('id', '=', $data['id'])->where('user_id', '=', $user->id)->first();
            if (!$habit) {
                return response()->json('Hábito del usuario no encontrado.', 404);
            }
        } else {
            $habit = new Habit();
            $habit->user_id = $user->id;
        }

        $habit->name = $data['name'];
        if ($data['order']) {
            $habit->order = $data['order'];
        }

        $habitSaved = $habit->save();

        return $habitSaved
            ? response()->json('Hábito del usuario guardado.')
            : response()->json('Error al guardar el hábito del usuario.', 500);
    }

    public function postHabitComplete(Request $request)
    {
        $data = $request->validate(['id' => 'required|integer']);

        /** @var User $user */
        $user = auth()->user();

        $habit = Habit::where('id', '=', $data['id'])->where('user_id', '=', $user->id)->first();
        if (!$habit) {
            return response()->json('Hábito del usuario no encontrado.', 404);
        }

        $habitComplete = new HabitComplete();
        $habitComplete->habit_id = $habit->id;

        $habitCompleteSaved = $habitComplete->save();

        return $habitCompleteSaved
            ? response()->json('Hábito completado del usuario guardado.')
            : response()->json('Error al guardar el hábito completado del usuario.', 500);
    }
}
