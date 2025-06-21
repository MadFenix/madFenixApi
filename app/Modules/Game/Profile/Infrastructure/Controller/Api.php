<?php


namespace App\Modules\Game\Profile\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Blockchain\Block\Domain\BlockchainHistorical;
use App\Modules\Blockchain\Block\Domain\Nft;
use App\Modules\Blockchain\Block\Domain\NftIdentification;
use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\Game\Profile\Transformers\Profile as ProfileTransformer;
use App\Modules\Habit\Domain\Habit;
use App\Modules\Habit\Domain\HabitComplete;
use App\Modules\User\Domain\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * @group Profile management
 *
 * APIs for managing profiles
 */
class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\Profile';
    }

    /**
     * Display a listing of profiles.
     *
     * Get a paginated list of all user profiles.
     *
     * @param Request $request
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter profiles by description. Example: "Aprendiz"
     * @bodyParam sorting string Sort profiles by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter profiles by parent ID. Example: 1
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * Store a newly created profile.
     *
     * Create a new user profile with the provided data.
     *
     * @bodyParam user_id integer required The ID of the user this profile belongs to. Example: 1
     * @bodyParam description string required The description/status of the profile (4-255 chars). Example: "Aprendiz"
     * @bodyParam details string Additional details about the profile. Example: "Joined during Season 1"
     * @bodyParam avatar string required The avatar URL or identifier (4-255 chars). Example: "avatar1.jpg"
     * @bodyParam plumas_hedera integer The number of plumas (feathers) on Hedera. Example: 0
     * @bodyParam plumas integer The number of plumas (feathers). Example: 10
     * @bodyParam season_level integer The current season level. Example: 1
     * @bodyParam season_points integer The current season points. Example: 100
     * @bodyParam oro_hedera integer The number of oro (gold) on Hedera. Example: 0
     * @bodyParam oro integer The number of oro (gold). Example: 5
     * @bodyParam twitch_user_id string The Twitch user ID. Example: "12345678"
     * @bodyParam twitch_user_name string The Twitch username. Example: "twitchuser"
     * @bodyParam twitch_api_user_token string The Twitch API user token. Example: "abc123token"
     * @bodyParam twitch_api_user_refresh_token string The Twitch API refresh token. Example: "abc123refresh"
     * @bodyParam twitch_scope string The Twitch API scope. Example: "user:read:email"
     * @bodyParam steam_user_id string The Steam user ID. Example: "76561198123456789"
     * @bodyParam steam_user_name string The Steam username. Example: "steamuser"
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        return parent::store();
    }

    /**
     * Display the specified profile.
     *
     * Get details of a specific profile by ID.
     *
     * @param string $account
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($account, $id)
    {
        return parent::show($account, $id);
    }

    /**
     * Update the specified profile.
     *
     * Update an existing profile with the provided data.
     *
     * @param string $account
     * @param int $id
     * @bodyParam user_id integer required The ID of the user this profile belongs to. Example: 1
     * @bodyParam description string required The description/status of the profile (4-255 chars). Example: "Aprendiz"
     * @bodyParam details string Additional details about the profile. Example: "Joined during Season 1"
     * @bodyParam avatar string required The avatar URL or identifier (4-255 chars). Example: "avatar1.jpg"
     * @bodyParam plumas_hedera integer The number of plumas (feathers) on Hedera. Example: 0
     * @bodyParam plumas integer The number of plumas (feathers). Example: 10
     * @bodyParam season_level integer The current season level. Example: 1
     * @bodyParam season_points integer The current season points. Example: 100
     * @bodyParam oro_hedera integer The number of oro (gold) on Hedera. Example: 0
     * @bodyParam oro integer The number of oro (gold). Example: 5
     * @bodyParam twitch_user_id string The Twitch user ID. Example: "12345678"
     * @bodyParam twitch_user_name string The Twitch username. Example: "twitchuser"
     * @bodyParam twitch_api_user_token string The Twitch API user token. Example: "abc123token"
     * @bodyParam twitch_api_user_refresh_token string The Twitch API refresh token. Example: "abc123refresh"
     * @bodyParam twitch_scope string The Twitch API scope. Example: "user:read:email"
     * @bodyParam steam_user_id string The Steam user ID. Example: "76561198123456789"
     * @bodyParam steam_user_name string The Steam username. Example: "steamuser"
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($account, $id)
    {
        return parent::update($account, $id);
    }

    /**
     * Remove the specified profile.
     *
     * Delete a profile by ID.
     *
     * @param string $account
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($account, Request $request)
    {
        return parent::destroy($account, $request);
    }

    /**
     * Download profiles as CSV or JSON.
     *
     * Export the profile data in CSV or JSON format.
     *
     * @param Request $request
     * @bodyParam type string The file format to download (csv or json). Example: "csv"
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter profiles by description. Example: "Aprendiz"
     * @bodyParam sorting string Sort profiles by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter profiles by parent ID. Example: 1
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Request $request)
    {
        return parent::download($request);
    }

    /**
     * List the fields of the profile model.
     *
     * Get the structure and field types of the profile model.
     *
     * @param string $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function fields($account)
    {
        return parent::fields($account);
    }

    /**
     * Get admin dashboard statistics.
     *
     * Retrieve platform-wide statistics for the admin dashboard.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminDashboard()
    {
        $return = new \stdClass();
        $return->totalPlumas = Profile::sum('plumas') + Profile::sum('plumas_hedera');
        $return->totalOro = Profile::sum('oro') + Profile::sum('oro_hedera');
        $return->totalSalesMonth = abs(BlockchainHistorical::whereDate('created_at', '>=', Carbon::now()->startOfMonth())->where('piezas_de_oro_ft', '<', 0)->sum('piezas_de_oro_ft'));
        $return->totalSalesYear = abs(BlockchainHistorical::whereDate('created_at', '>=', Carbon::now()->startOfYear())->where('piezas_de_oro_ft', '<', 0)->sum('piezas_de_oro_ft'));

        $return->totalPlumasLast10Days = new \stdClass();
        $return->totalPlumasLast10Days->todayMinus0 = BlockchainHistorical::whereDate('created_at', Carbon::today())->sum('plumas');
        $return->totalPlumasLast10Days->todayMinus1 = BlockchainHistorical::whereDate('created_at', Carbon::yesterday())->sum('plumas');
        $return->totalPlumasLast10Days->todayMinus2 = BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(2))->sum('plumas');
        $return->totalPlumasLast10Days->todayMinus3 = BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(3))->sum('plumas');
        $return->totalPlumasLast10Days->todayMinus4 = BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(4))->sum('plumas');
        $return->totalPlumasLast10Days->todayMinus5 = BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(5))->sum('plumas');
        $return->totalPlumasLast10Days->todayMinus6 = BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(6))->sum('plumas');
        $return->totalPlumasLast10Days->todayMinus7 = BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(7))->sum('plumas');
        $return->totalPlumasLast10Days->todayMinus8 = BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(8))->sum('plumas');
        $return->totalPlumasLast10Days->todayMinus9 = BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(9))->sum('plumas');

        $return->plumasLast10Changes = [];
        $records = BlockchainHistorical::where('plumas', '!=', 0)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        foreach ($records as $record) {
            $profile = Profile::where('user_id', '=', $record->user_id)->first();
            $return->plumasLast10Changes[] = [
                'username' => $record->user->name,
                'avatar' => $profile->avatar,
                'state' => $profile->description,
                'plumas' => $record->plumas,
                'memo' => $record->memo,
                'created_at' => $record->created_at,
            ];
        }

        $return->totalOroLast10Days = new \stdClass();
        $return->totalOroLast10Days->todayMinus0 = BlockchainHistorical::whereDate('created_at', Carbon::today())->sum('piezas_de_oro_ft');
        $return->totalOroLast10Days->todayMinus1 = BlockchainHistorical::whereDate('created_at', Carbon::yesterday())->sum('piezas_de_oro_ft');
        $return->totalOroLast10Days->todayMinus2 = BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(2))->sum('piezas_de_oro_ft');
        $return->totalOroLast10Days->todayMinus3 = BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(3))->sum('piezas_de_oro_ft');
        $return->totalOroLast10Days->todayMinus4 = BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(4))->sum('piezas_de_oro_ft');
        $return->totalOroLast10Days->todayMinus5 = BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(5))->sum('piezas_de_oro_ft');
        $return->totalOroLast10Days->todayMinus6 = BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(6))->sum('piezas_de_oro_ft');
        $return->totalOroLast10Days->todayMinus7 = BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(7))->sum('piezas_de_oro_ft');
        $return->totalOroLast10Days->todayMinus8 = BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(8))->sum('piezas_de_oro_ft');
        $return->totalOroLast10Days->todayMinus9 = BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(9))->sum('piezas_de_oro_ft');

        $return->oroLast10Changes = [];
        $records = BlockchainHistorical::where('piezas_de_oro_ft', '!=', 0)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        foreach ($records as $record) {
            $profile = Profile::where('user_id', '=', $record->user_id)->first();
            $return->oroLast10Changes[] = [
                'username' => $record->user->name,
                'avatar' => $profile->avatar,
                'state' => $profile->description,
                'oro' => $record->piezas_de_oro_ft,
                'memo' => $record->memo,
                'created_at' => $record->created_at,
            ];
        }

        $return->totalSalesLast10Days = new \stdClass();
        $return->totalSalesLast10Days->todayMinus0 = abs(BlockchainHistorical::whereDate('created_at', Carbon::today())->where('piezas_de_oro_ft', '<', 0)->sum('piezas_de_oro_ft'));
        $return->totalSalesLast10Days->todayMinus1 = abs(BlockchainHistorical::whereDate('created_at', Carbon::yesterday())->where('piezas_de_oro_ft', '<', 0)->sum('piezas_de_oro_ft'));
        $return->totalSalesLast10Days->todayMinus2 = abs(BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(2))->where('piezas_de_oro_ft', '<', 0)->sum('piezas_de_oro_ft'));
        $return->totalSalesLast10Days->todayMinus3 = abs(BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(3))->where('piezas_de_oro_ft', '<', 0)->sum('piezas_de_oro_ft'));
        $return->totalSalesLast10Days->todayMinus4 = abs(BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(4))->where('piezas_de_oro_ft', '<', 0)->sum('piezas_de_oro_ft'));
        $return->totalSalesLast10Days->todayMinus5 = abs(BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(5))->where('piezas_de_oro_ft', '<', 0)->sum('piezas_de_oro_ft'));
        $return->totalSalesLast10Days->todayMinus6 = abs(BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(6))->where('piezas_de_oro_ft', '<', 0)->sum('piezas_de_oro_ft'));
        $return->totalSalesLast10Days->todayMinus7 = abs(BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(7))->where('piezas_de_oro_ft', '<', 0)->sum('piezas_de_oro_ft'));
        $return->totalSalesLast10Days->todayMinus8 = abs(BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(8))->where('piezas_de_oro_ft', '<', 0)->sum('piezas_de_oro_ft'));
        $return->totalSalesLast10Days->todayMinus9 = abs(BlockchainHistorical::whereDate('created_at', Carbon::now()->subDays(9))->where('piezas_de_oro_ft', '<', 0)->sum('piezas_de_oro_ft'));

        return response()->json($return);
    }

    /**
     * Subtract plumas from user.
     *
     * Deduct a specified amount of plumas (feathers) from the current user's profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function subtractPlumaUser()
    {
        /** @var User $user */
        $user = auth()->user();

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }

        $profile->plumas--;
        $profileSaved = $profile->save();

        $newBlockchainHistorical = new BlockchainHistorical();
        $newBlockchainHistorical->user_id = $user->id;
        $newBlockchainHistorical->plumas = -1;
        $newBlockchainHistorical->memo = "Used";
        $blockchainHistoricalSaved = $newBlockchainHistorical->save();

        return $profileSaved && $blockchainHistoricalSaved
            ? response()->json('Se ha restado la pluma del usuario.')
            : response()->json('Error al guardar el perfil.', 500);
    }

    /**
     * Subtract oro from user.
     *
     * Deduct a specified amount of oro (gold) from the current user's profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function subtractOroUser()
    {
        /** @var User $user */
        $user = auth()->user();

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }

        $profile->oro--;
        $profileSaved = $profile->save();

        $newBlockchainHistorical = new BlockchainHistorical();
        $newBlockchainHistorical->user_id = $user->id;
        $newBlockchainHistorical->piezas_de_oro_ft = -1;
        $newBlockchainHistorical->memo = "Used";
        $blockchainHistoricalSaved = $newBlockchainHistorical->save();

        return $profileSaved && $blockchainHistoricalSaved
            ? response()->json('Se ha restado el oro del usuario.')
            : response()->json('Error al guardar el perfil.', 500);
    }

    /**
     * Set user avatar.
     *
     * Update the avatar for the current user's profile.
     *
     * @param Request $request
     * @bodyParam avatar string required The new avatar URL or identifier. Example: "avatar2.jpg"
     * @return \Illuminate\Http\JsonResponse
     */
    public function setAvatar(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $data = $request->validate([
            'nft_id' => 'required'
        ]);

        $nft = Nft::find($data['nft_id']);
        if (!$nft || $nft->category != 'Avatar') {
            return response()->json('Avatar no encontrado.', 404);
        }

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }

        $profile->avatar = $nft->portrait_image;
        $profileSaved = $profile->save();

        return $profileSaved
            ? response()->json('Se ha establecido tu nuevo avatar.')
            : response()->json('Error al guardar el perfil.', 500);
    }

    /**
     * Set user status.
     *
     * Update the status/description for the current user's profile.
     *
     * @param Request $request
     * @bodyParam estado string required The new status/description for the profile. Example: "Explorador"
     * @return \Illuminate\Http\JsonResponse
     */
    public function setEstado(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $data = $request->validate([
            'nft_id' => 'required'
        ]);

        $nft = Nft::find($data['nft_id']);
        if (!$nft || $nft->category != 'Estado') {
            return response()->json('Estado no encontrado.', 404);
        }

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }

        $profile->description = $nft->name;
        $profileSaved = $profile->save();

        return $profileSaved
            ? response()->json('Se ha establecido tu nuevo estado.')
            : response()->json('Error al guardar el perfil.', 500);
    }

    /**
     * Get current user's profile.
     *
     * Retrieve detailed information about the authenticated user's profile, including habits, NFTs, and other statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserProfile()
    {
        /** @var User $user */
        $user = auth()->user();

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }

        $nftIdentifications = NftIdentification::where('user_id', '=', $user->id)->get();
        $nftIdentificationsHedera = NftIdentification::where('user_id_hedera', '=', $user->id)->get();

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

        $returnProfile = new \stdClass();
        $returnProfile->username = $user->name;
        $returnProfile->email = $user->email;
        $returnProfile->description = $profile->description;
        $returnProfile->details = $profile->details;
        $returnProfile->avatar = $profile->avatar;
        $returnProfile->plumas = $profile->plumas;
        $returnProfile->plumas_hedera = (empty($profile->plumas_hedera))? 0 : $profile->plumas_hedera;
        $returnProfile->oro = $profile->oro;
        $returnProfile->oro_hedera = (empty($profile->oro_hedera))? 0 : $profile->oro_hedera;
        $returnProfile->user_twitch = $profile->twitch_user_name;
        $returnProfile->user_steam = $profile->steam_user_name;
        $returnProfile->referred_code = $profile->referred_code;
        $returnProfile->referred_code_from = $profile->referred_code_from;
        $returnProfile->hedera_wallet = $profile->hedera_wallet;
        $returnProfile->hedera_wallet_check = $profile->hedera_wallet_check;
        $returnProfile->hedera_wallet_check_account = $profile->hedera_wallet_check_account;
        $returnProfile->nfts = [];
        $returnProfile->nfts_hedera = [];
        if ($profile->referred_code) {
            $returnProfile->count_refered = Profile::where('referred_code_from', '=', $profile->referred_code)->count();
        } else {
            $returnProfile->count_refered = 0;
        }
        $returnProfile->fighter_minimum_version = '0.1';
        $nfts = Nft::all();
        $returnProfile->nft_categories = [];
        foreach ($nfts as $nft) {
            if (!$nft->category) {
                continue;
            }
            $keyCategoryExists = null;
            foreach ($returnProfile->nft_categories as $keyCategory => $checkCategory) {
                if ($nft->category == $checkCategory->name) {
                    $keyCategoryExists = $keyCategory;
                    break;
                }
            }
            if ($keyCategoryExists === null) {
                $newNftCategory = new \stdClass();
                $newNftCategory->name = $nft->category;
                $newNftCategory->subcategories = [];
                $newNftCategory->nfts = [];
                $newNftCategory->nfts[] = (object) $nft->toArray();
                $returnProfile->nft_categories[] = $newNftCategory;
            } else {
                $returnProfile->nft_categories[$keyCategoryExists]->nfts[] = (object) $nft->toArray();
            }
        }
        foreach ($nfts as $nft) {
            $subcategoryExists = false;
            $keyCategoryExists = null;
            foreach ($returnProfile->nft_categories as $keyCategory => $checkCategory) {
                if ($nft->category == $checkCategory->name) {
                    $keyCategoryExists = $keyCategory;
                    foreach ($checkCategory->subcategories as $checkSubcategory) {
                        if ($nft->subcategory == $checkSubcategory) {
                            $subcategoryExists = true;
                            break;
                        }
                    }
                    break;
                }
            }
            if ($subcategoryExists || $keyCategoryExists === null || !$nft->subcategory) {
                continue;
            }
            $returnProfile->nft_categories[$keyCategoryExists]->subcategories[] = $nft->subcategory;
        }
        foreach ($nftIdentifications as $nftIdentification) {
            $nft = null;
            foreach ($nfts as $nftSearch) {
                if ($nftSearch->id == $nftIdentification->nft_id) {
                    $nft = $nftSearch;
                }
            }
            if (!$nft) {
                $nft = Nft::find($nftIdentification->nft_id);
            }
            if (empty($nft)) {
                $nft = new Nft();
            }
            $newNft = (object) $nftIdentification->toArray();
            $newNft->nft = (object) $nft->toArray();
            $returnProfile->nfts[] = $newNft;
        }
        foreach ($nftIdentificationsHedera as $nftIdentification) {
            $nft = null;
            foreach ($nfts as $nftSearch) {
                if ($nftSearch->id == $nftIdentification->nft_id) {
                    $nft = $nftSearch;
                }
            }
            if (!$nft) {
                $nft = Nft::find($nftIdentification->nft_id);
            }
            if (empty($nft)) {
                $nft = new Nft();
            }
            $newNft = (object) $nftIdentification->toArray();
            $newNft->nft = (object) $nft->toArray();
            $returnProfile->nfts_hedera[] = $newNft;
        }
        $returnProfile->habits = [];
        foreach ($userHabits as $userHabit) {
            $newHabit = (object) $userHabit->toArray();
            $newHabit->habit_completed = in_array($userHabit->id, $userHabitCompletedIds);
            $returnProfile->habits[] = $newHabit;
        }


        return response()->json($returnProfile);
    }

    /**
     * Set user's referral code.
     *
     * Update the referral code for the current user's profile.
     *
     * @param Request $request
     * @bodyParam referred_code string required The referral code to set. Example: "REF123"
     * @return \Illuminate\Http\JsonResponse
     */
    public function setUserProfileReferredCode(Request $request)
    {
        $data = $request->validate(['referred_code' => 'required|string']);

        /** @var User $user */
        $user = auth()->user();

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }

        $profileWithSameReferredCode = Profile::where('referred_code', '=', $data['referred_code'])->first();
        if ($profileWithSameReferredCode) {
            return response()->json('Ya existe un usuario con este código de referido.', 400);
        }

        $profile->referred_code = $data['referred_code'];
        $profileSaved = $profile->save();

        return $profileSaved
            ? response()->json('Se ha establecido tu código de referido.')
            : response()->json('Error al guardar el perfil.', 500);
    }

    /**
     * Set Hedera wallet verification status.
     *
     * Update the Hedera wallet verification status for the current user's profile.
     *
     * @param Request $request
     * @bodyParam hedera_wallet_check boolean required Whether the Hedera wallet is verified. Example: true
     * @bodyParam hedera_wallet_check_account string required The Hedera account that verified the wallet. Example: "0.0.123456"
     * @return \Illuminate\Http\JsonResponse
     */
    public function setUserProfileHederaWalletCheck(Request $request)
    {
        $data = $request->validate(['account' => 'required|string']);

        /** @var User $user */
        $user = auth()->user();

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }

        $hederaAccount = explode('-', $data['account']);
        if (empty($hederaAccount[0])) {
            return response()->json('La wallet de hedera no es válida.', 400);
        }

        if (!empty($profile->hedera_wallet_check)) {
            return response()->json('Ya existe un decimal de comprobación para validar la wallet de hedera.', 400);
        }

        $profile->hedera_wallet_check = rand(229, 858) . '00000';
        $profile->hedera_wallet_check_account = $hederaAccount[0];
        $profileSaved = $profile->save();

        return $profileSaved
            ? response()->json($profile->hedera_wallet_check)
            : response()->json('Error al guardar el perfil.', 500);
    }

    /**
     * Set referral code source.
     *
     * Update the referral code source for the current user's profile.
     *
     * @param Request $request
     * @bodyParam referred_code_from string required The referral code that referred this user. Example: "FRIEND123"
     * @return \Illuminate\Http\JsonResponse
     */
    public function setUserProfileReferredCodeFrom(Request $request)
    {
        $data = $request->validate(['referred_code_from' => 'required|string']);

        /** @var User $user */
        $user = auth()->user();

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }
        if ($profile->season_level < 2) {
            return response()->json('Para vincular un referido debes tener el nivel dos del pase de temporada.', 404);
        }
        if ($profile->referred_code_from) {
            return response()->json('Ya tienes un código de referido.', 400);
        }

        $profileWithSameReferredCode = Profile::where('referred_code', '=', $data['referred_code_from'])->first();
        if (!$profileWithSameReferredCode) {
            return response()->json('No existe ningún usuario con este código de referido.', 404);
        }

        $oroToReferred = 5;

        $profile->referred_code_from = $data['referred_code_from'];
        $profile->oro += $oroToReferred;
        $profileSaved = $profile->save();

        $newBlockchainHistorical = new BlockchainHistorical();
        $newBlockchainHistorical->user_id = $profile->user_id;
        $newBlockchainHistorical->piezas_de_oro_ft = $oroToReferred;
        $newBlockchainHistorical->memo = "Attach referred code from. User " . $profileWithSameReferredCode->user_id;
        $blockchainHistoricalSaved = $newBlockchainHistorical->save();

        $profileWithSameReferredCode->oro += $oroToReferred;
        $profileSaved2 = $profileWithSameReferredCode->save();

        $newBlockchainHistorical2 = new BlockchainHistorical();
        $newBlockchainHistorical2->user_id = $profileWithSameReferredCode->user_id;
        $newBlockchainHistorical2->piezas_de_oro_ft = $oroToReferred;
        $newBlockchainHistorical2->memo = "New referred. User " . $profile->user_id;
        $blockchainHistoricalSaved2 = $newBlockchainHistorical2->save();

        return $profileSaved && $profileSaved2 && $blockchainHistoricalSaved && $blockchainHistoricalSaved2
            ? response()->json('Se ha establecido el código del usuario referido en tu perfil.')
            : response()->json('Error al guardar el perfil.', 500);
    }

    /**
     * Add plumas to user.
     *
     * Add a specified amount of plumas (feathers) to the current user's profile.
     *
     * @param Request $request
     * @bodyParam plumas integer required The amount of plumas to add. Example: 10
     * @return \Illuminate\Http\JsonResponse
     */
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

        $newBlockchainHistorical = new BlockchainHistorical();
        $newBlockchainHistorical->user_id = $data['user_id'];
        $newBlockchainHistorical->plumas = $data['plumas'];
        $newBlockchainHistorical->memo = "Admin decision";
        $blockchainHistoricalSaved = $newBlockchainHistorical->save();

        return $profileSaved && $blockchainHistoricalSaved
            ? response()->json('Se han sumado las plumas al usuario.')
            : response()->json('Error al guardar el perfil.', 500);
    }

    /**
     * Add oro to user.
     *
     * Add a specified amount of oro (gold) to the current user's profile.
     *
     * @param Request $request
     * @bodyParam user_id integer required The ID of the user to add oro to. Example: 1
     * @bodyParam oro integer required The amount of oro to add. Example: 5
     * @return \Illuminate\Http\JsonResponse
     */
    public function addOro(Request $request)
    {
        $data = $request->validate(['user_id' => 'required|integer', 'oro' => 'required|integer']);
        /** @var User $user */
        $user = auth()->user();

        if ($user->email != 'iam@valentigamez.com') {
            return response()->json('Solo el administrador puede ejecutar esta función.', 403);
        }

        $profile = Profile::where('user_id', '=', $data['user_id'])->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }

        $profile->oro += $data['oro'];
        $profileSaved = $profile->save();

        $newBlockchainHistorical = new BlockchainHistorical();
        $newBlockchainHistorical->user_id = $data['user_id'];
        $newBlockchainHistorical->piezas_de_oro_ft = $data['piezas_de_oro_ft'];
        $newBlockchainHistorical->memo = "Admin decision";
        $blockchainHistoricalSaved = $newBlockchainHistorical->save();

        return $profileSaved && $blockchainHistoricalSaved
            ? response()->json('Se ha sumado el oro al usuario.')
            : response()->json('Error al guardar el perfil.', 500);
    }

}
