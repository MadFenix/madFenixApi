<?php

namespace App\Console\Commands;

use App\Modules\Blockchain\Block\Domain\BlockchainHistorical;
use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\Game\Ranking\Domain\Tournament;
use App\Modules\Game\Ranking\Domain\TournamentUser;
use App\Modules\User\Domain\User;
use Illuminate\Console\Command;

class EnrollToTournamentCheckHederaId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enroll-to-tournament-check-hedera-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enroll users to Tournament chcking hedera id';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $tournament = Tournament::findOrFail(4);
        $userEmails = json_decode('[{"email":"test@gmail.com", "id":"0.0.0000"}]');
        $hederaIdsAndQuantity = json_decode('[{"id":"0.0.0000","quantity":0}]');

        $totalInscritos = 0;
        $totalNoInscritos = 0;
        foreach ($userEmails as $userEmail) {
            $userFind = false;
            $nftQuantity = 1;
            foreach ($hederaIdsAndQuantity as $hederaIdAndQuantity) {
                if ($hederaIdAndQuantity->id == $userEmail->id) {
                    $userFind = true;
                    $nftQuantity = $hederaIdAndQuantity->quantity;
                }
            }
            if (!$userFind) {
                $totalNoInscritos++;
                $this->line('No inscrito. Usuario: ' . $userEmail->email . '.');
                continue;
            }

            $user = User::where('email', '=', $userEmail->email)->first();
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
            $newTournamentUser->save();
            $totalInscritos++;
            $this->line('Inscripción. Usuario: ' . $userEmail->email . '.');

            $plumasASumar = intdiv($nftQuantity, 5);
            if ($plumasASumar > 0) {
                $profile = Profile::where('user_id', '=', $user->id)->first();
                if ($profile) {
                    $profile->plumas += $plumasASumar;
                    $profile->save();
                }

                $newBlockchainHistorical = new BlockchainHistorical();
                $newBlockchainHistorical->user_id = $user->id;
                $newBlockchainHistorical->plumas = $plumasASumar;
                $newBlockchainHistorical->memo = 'Torneo 2 Elevado se juega en Hedera';
                $newBlockchainHistorical->save();

                $this->line('Ingreso. Usuario: ' . $userEmail->email . '. Plumas: ' . $plumasASumar);
            }
        }
        $this->line('Inscritos: ' . $totalInscritos . '.');
        $this->line('No inscritos: ' . $totalNoInscritos . '.');
    }
}
