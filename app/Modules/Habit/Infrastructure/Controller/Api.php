<?php


namespace App\Modules\Habit\Infrastructure\Controller;

use App\Http\Controllers\Controller;
use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\Game\Season\Domain\Season;
use App\Modules\Game\Season\Domain\SeasonReward;
use App\Modules\Habit\Domain\Habit;
use App\Modules\Habit\Domain\HabitComplete;
use App\Modules\Habit\Infrastructure\ApiHelix;
use App\Modules\User\Domain\User;
use Carbon\Carbon;
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

        $userHabits = Habit::where('user_id', '=', $user->id)->orderBy('order')->get();
        $userHabitIds = [];
        foreach ($userHabits as $userHabit) {
            $userHabitIds[] = $userHabit->id;
        }
        $dateNow = Carbon::now();
        $dateNow->startOfDay();
        $userHabitCompletes = HabitComplete::where('created_at', '>', $dateNow->format('Y-m-d H:i:s'))->whereIn('habit_id', $userHabitIds)->get();
        $userHabitCompletedIds = [];
        foreach ($userHabitCompletes as $userHabitComplete) {
            $userHabitCompletedIds[] = $userHabitComplete->habit_id;
        }
        if (count($userHabitIds) == count($userHabitCompletedIds)) {
            $profile = Profile::where('user_id', '=', $user->id)->first();
            if (!$profile) {
                return response()->json('Perfil del usuario no encontrado.', 404);
            }

            $profile->season_points += 25000;

            $dateNow = Carbon::now();
            $activeSeason = Season::where('start_date', '<', $dateNow->format('Y-m-d H:i:s'))
                ->where('end_date', '>', $dateNow->format('Y-m-d H:i:s'))
                ->first();
            if ($activeSeason) {
                $lastSeasonReward = SeasonReward::where('season_id', '=', $activeSeason->id)
                    ->where('required_points', '<', $profile->season_points)
                    ->orderByDesc('level')
                    ->first();
                if ($lastSeasonReward) {
                    $profile->season_level = $lastSeasonReward->level;
                }
            }

            $profile->save();
        }

        return $habitCompleteSaved
            ? response()->json('Hábito completado del usuario guardado.')
            : response()->json('Error al guardar el hábito completado del usuario.', 500);
    }
}
