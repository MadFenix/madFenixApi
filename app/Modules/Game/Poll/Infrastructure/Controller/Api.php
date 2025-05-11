<?php


namespace App\Modules\Game\Poll\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Blockchain\Block\Domain\NftIdentification;
use App\Modules\Game\Poll\Domain\PollAnswer;
use App\Modules\Game\Poll\Infrastructure\Service\PollUtilities;
use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\Game\Poll\Domain\Poll;
use App\Modules\Game\Poll\Domain\PollReward;
use App\Modules\Game\Poll\Domain\PollRewardRedeemed;
use App\Modules\Game\Poll\Infrastructure\Service\UserPollPremium;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\Poll';
    }

    public function pollDetails(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'poll_id' => 'required',
            'active' => 'required'
        ]);

        $active = true;
        if (empty($data['active'])) {
            $active = false;
        }
        try {
            $pollDetails = PollUtilities::pollDetails($data['poll_id'], $active, $user);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }

        return response()->json($pollDetails);
    }

    public function pollsDetailsLast30Days()
    {
        $user = auth()->user();

        $dateNow = Carbon::now();
        $date = Carbon::now();
        $date->subDays(30);
        $polls = Poll::where('start_date', '<', $dateNow->format('Y-m-d H:i:s'))
            ->where('end_date', '>', $date->format('Y-m-d H:i:s'))
            ->orderBy('start_date', 'DESC')
            ->get();
        $pollsReturn = [];
        foreach ($polls as $poll) {
            try {
                $pollsReturn[] = PollUtilities::pollDetails($poll->id, false, $user, $poll);
            } catch (\Exception $e) {
                return response()->json($e->getMessage(), 500);
            }
        }

        return response()->json($pollsReturn);
    }

    public function answerPoll(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'poll_id' => 'required',
            'answer' => 'required',
        ]);
        $answer = $data['answer'];
        $poll_id = $data['poll_id'];

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }
        $userAnswer = PollAnswer::where('poll_id', '=', $poll_id)
            ->where('user_id', '=', $user->id)
            ->first();
        if ($userAnswer) {
            return response()->json('Ya se ha registrado una respuesta con tu usuario en esta encuesta.', 403);
        }

        $dateNow = Carbon::now();
        $activePoll = Poll::where('start_date', '<', $dateNow->format('Y-m-d H:i:s'))
            ->where('end_date', '>', $dateNow->format('Y-m-d H:i:s'))
            ->where('id', '=', $poll_id)
            ->first();
        if (!$activePoll) {
            return response()->json('No se ha encontrado la poll.', 404);
        }

        $cronistas = NftIdentification::where('nft_id', '=', 34)
            ->where(function ($query) use($user) {
                $query->where('user_id', '=', $user->id)
                    ->orWhere('user_id_hedera', '=', $user->id);
            })
            ->count();
        $plumas = $profile->plumas + $profile->plumas_hedera;

        $pollAnswer = new PollAnswer();
        $pollAnswer->poll_id = $activePoll->id;
        $pollAnswer->user_id = $user->id;
        $pollAnswer->plumas = $plumas;
        $pollAnswer->cronistas = $cronistas;
        $pollAnswer->answer = $answer;
        $pollAnswerSaved = $pollAnswer->save();

        return $pollAnswerSaved
            ? response()->json('Se ha registrado la respuesta.')
            : response()->json('Error al registrar la respuesta.', 500);
    }
}
