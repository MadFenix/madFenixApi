<?php

namespace App\Modules\Game\Poll\Infrastructure\Service;

use App\Modules\Game\Poll\Domain\Poll;
use App\Modules\Game\Poll\Domain\PollAnswer;
use Carbon\Carbon;

class PollUtilities
{
    static public function pollDetails($poll_id, $active = true, $user = null, $poll = null) {
        $dateNow = Carbon::now();
        if (!$poll) {
            if ($active) {
                $poll = Poll::where('start_date', '<', $dateNow->format('Y-m-d H:i:s'))
                    ->where('end_date', '>', $dateNow->format('Y-m-d H:i:s'))
                    ->where('id', '=', $poll_id)
                    ->first();
                if (!$poll) {
                    throw new \Exception('No se ha encontrado la encuesta.');
                }
            } else {
                $poll = Poll::where('id', '=', $poll_id)
                    ->first();
            }
        }
        $pollDetails = (object) $poll->toArray();
        if ($poll->end_date >= $dateNow && $poll->start_date <= $dateNow) {
            $pollDetails->active = true;
        } else {
            $pollDetails->active = false;
        }

        $pollAnswers = PollAnswer::where('poll_id', '=', $poll->id)
            ->get();

        $pollDetails->answers = [];
        $pollDetails->customAnswers = [];
        if (empty($poll->answers)) {
            foreach ($pollAnswers as $pollAnswer) {
                $newPollAnswer = (object) $pollAnswer->toArray();
                $newPollAnswer->username = $pollAnswer->user->name;

                $pollDetails->customAnswers[] = $newPollAnswer;
            }
        } else {
            $answers = explode(",", $poll->answers);

            foreach ($answers as $keyAnswer => $answer) {
                $newAnswer = new \stdClass();
                $newAnswer->description = $answer;
                $newAnswer->plumas = 0;
                $newAnswer->cronistas = 0;
                $newAnswer->votes = 0;
                $pollDetails->answers[$keyAnswer] = $newAnswer;
            }

            foreach ($pollAnswers as $pollAnswer) {
                foreach ($answers as $keyAnswer => $answer) {
                    if ($answer == $pollAnswer->answer) {
                        $plumas = (empty($pollAnswer->plumas))? 0 : $pollAnswer->plumas;
                        $pollDetails->answers[$keyAnswer]->plumas += $plumas;
                        $cronistas = (empty($pollAnswer->cronistas))? 0 : $pollAnswer->cronistas;
                        $pollDetails->answers[$keyAnswer]->cronistas += $cronistas;
                        break;
                    }
                }
            }

            $totalPlumas = 0;
            $totalCronistas = 0;
            foreach ($answers as $keyAnswer => $answer) {
                $totalPlumas += $pollDetails->answers[$keyAnswer]->plumas;
                $totalCronistas += $pollDetails->answers[$keyAnswer]->cronistas;
            }

            $dividerPlumas = 2;
            if (empty($totalCronistas)) {
                $dividerPlumas = 1;
            }
            $dividerCronistas = 2;
            if (empty($totalPlumas)) {
                $dividerCronistas = 1;
            }
            foreach ($answers as $keyAnswer => $answer) {
                if (!empty($totalPlumas)) {
                    $pollDetails->answers[$keyAnswer]->votes += (($pollDetails->answers[$keyAnswer]->plumas / $totalPlumas) * 100) / $dividerPlumas;
                }
                if (!empty($totalCronistas)) {
                    $pollDetails->answers[$keyAnswer]->votes += (($pollDetails->answers[$keyAnswer]->cronistas / $totalCronistas) * 100) / $dividerCronistas;
                }
            }

            foreach ($answers as $keyAnswer => $answer) {
                $pollDetails->answers[$keyAnswer]->votes = number_format($pollDetails->answers[$keyAnswer]->votes, 2);
            }
        }

        $pollDetails->userAnswer = null;
        if ($user) {
            $userAnswer = PollAnswer::where('poll_id', '=', $poll_id)
                ->where('user_id', '=', $user->id)
                ->first();
            if ($userAnswer) {
                $pollDetails->userAnswer = (object) $userAnswer->toArray();
                $pollDetails->userAnswer->username = $userAnswer->user->name;

                $pollDetails->userAnswer->votes = 0;
                if (!empty($poll->answers)) {
                    foreach ($answers as $keyAnswer => $answer) {
                        if ($answer == $pollDetails->userAnswer->answer) {
                            if (!empty($totalPlumas)) {
                                $pollDetails->userAnswer->votes += (($pollDetails->userAnswer->plumas / $totalPlumas) * 100) / $dividerPlumas;
                            }
                            if (!empty($totalCronistas)) {
                                $pollDetails->userAnswer->votes += (($pollDetails->userAnswer->cronistas / $totalCronistas) * 100) / $dividerCronistas;
                            }
                            $pollDetails->userAnswer->votes = number_format($pollDetails->userAnswer->votes, 2);
                            break;
                        }
                    }
                }
            }
        }

        return $pollDetails;
    }
}
