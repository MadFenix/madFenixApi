<?php

namespace App\Console\Commands;

use App\Modules\Game\Ranking\Domain\Tournament;
use App\Modules\Game\Ranking\Domain\TournamentUser;
use App\Modules\User\Domain\User;
use Illuminate\Console\Command;

class EnrollToTournament extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enroll-to-tournament';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enroll users to Tournament';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $tournament = Tournament::findOrFail(3);
        $userEmails = [
        ];

        foreach ($userEmails as $userEmail) {
            $user = User::where('email', '=', $userEmail)->first();
            if (!$user) {
                continue;
            }

            $tournamentUser = TournamentUser::where('tournament_id', '=', $tournament->id)->where('user_id', '=', $user->id)->first();
            if ($tournamentUser) {
                continue;
            }

            $newTournamentUser = new TournamentUser();
            $newTournamentUser->tournament_id = $tournament->id;
            $newTournamentUser->user_id = $user->id;
            $newTournamentUser->max_points = 0;
            $newTournamentUser->max_time = 0;
            //$newTournamentUser->save();
        }
    }
}
