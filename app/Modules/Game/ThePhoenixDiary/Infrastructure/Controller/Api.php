<?php

namespace App\Modules\Game\ThePhoenixDiary\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Game\ThePhoenixDiary\Domain\TpdCharacter;
use App\Modules\Game\ThePhoenixDiary\Infrastructure\ThePhoenixDiaryUtilities;
use App\Modules\User\Domain\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @group Phoenix Diary management
 *
 * APIs for managing phoenix diary
 */
class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\ThePhoenixDiary';
    }

    /**
     * Display a listing of Phoenix Diary entries.
     *
     * Get a paginated list of all Phoenix Diary entries.
     *
     * @param Request $request
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter entries by name. Example: "Phoenix"
     * @bodyParam sorting string Sort entries by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter entries by parent ID. Example: 1
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * Store a newly created Phoenix Diary entry.
     *
     * Create a new Phoenix Diary entry with the provided data.
     *
     * @bodyParam nft_id integer The ID of the associated NFT. Example: 1
     * @bodyParam initial_object_id integer The ID of the initial object. Example: 2
     * @bodyParam type string The type of character. Example: "Hero"
     * @bodyParam subtype string The subtype of character. Example: "Warrior"
     * @bodyParam active integer Whether the character is active. Example: 1
     * @bodyParam name string The name of the character. Example: "Phoenix Warrior"
     * @bodyParam short_description string The short description of the character. Example: "A powerful warrior"
     * @bodyParam description string The detailed description of the character. Example: "A powerful warrior with fire abilities"
     * @bodyParam portrait_image string The portrait image URL. Example: "https://example.com/portrait.jpg"
     * @bodyParam featured_image string The featured image URL. Example: "https://example.com/featured.jpg"
     * @bodyParam tpd_entry_url string The entry URL. Example: "https://example.com/entry"
     * @bodyParam hp integer The health points. Example: 100
     * @bodyParam ad integer The attack damage. Example: 50
     * @bodyParam ap integer The ability power. Example: 30
     * @bodyParam def integer The defense. Example: 40
     * @bodyParam mr integer The magic resistance. Example: 20
     * @return JsonResponse
     */
    public function store()
    {
        return parent::store();
    }

    /**
     * Display the specified Phoenix Diary entry.
     *
     * Get details of a specific Phoenix Diary entry by ID.
     *
     * @param string $account
     * @param int $id
     * @return JsonResponse
     */
    public function show($account, $id)
    {
        return parent::show($account, $id);
    }

    /**
     * Update the specified Phoenix Diary entry.
     *
     * Update an existing Phoenix Diary entry with the provided data.
     *
     * @param string $account
     * @param int $id
     * @bodyParam nft_id integer The ID of the associated NFT. Example: 1
     * @bodyParam initial_object_id integer The ID of the initial object. Example: 2
     * @bodyParam type string The type of character. Example: "Hero"
     * @bodyParam subtype string The subtype of character. Example: "Warrior"
     * @bodyParam active integer Whether the character is active. Example: 1
     * @bodyParam name string The name of the character. Example: "Phoenix Warrior"
     * @bodyParam short_description string The short description of the character. Example: "A powerful warrior"
     * @bodyParam description string The detailed description of the character. Example: "A powerful warrior with fire abilities"
     * @bodyParam portrait_image string The portrait image URL. Example: "https://example.com/portrait.jpg"
     * @bodyParam featured_image string The featured image URL. Example: "https://example.com/featured.jpg"
     * @bodyParam tpd_entry_url string The entry URL. Example: "https://example.com/entry"
     * @bodyParam hp integer The health points. Example: 100
     * @bodyParam ad integer The attack damage. Example: 50
     * @bodyParam ap integer The ability power. Example: 30
     * @bodyParam def integer The defense. Example: 40
     * @bodyParam mr integer The magic resistance. Example: 20
     * @return JsonResponse
     */
    public function update($account, $id)
    {
        return parent::update($account, $id);
    }

    /**
     * Remove the specified Phoenix Diary entry.
     *
     * Delete a Phoenix Diary entry by ID.
     *
     * @param string $account
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy($account, Request $request)
    {
        return parent::destroy($account, $request);
    }

    /**
     * Download Phoenix Diary entries as CSV or JSON.
     *
     * Export the Phoenix Diary entry data in CSV or JSON format.
     *
     * @param Request $request
     * @bodyParam type string The file format to download (csv or json). Example: "csv"
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter entries by name. Example: "Phoenix"
     * @bodyParam sorting string Sort entries by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter entries by parent ID. Example: 1
     * @return JsonResponse|StreamedResponse
     */
    public function download(Request $request)
    {
        return parent::download($request);
    }

    /**
     * List the fields of the Phoenix Diary model.
     *
     * Get the structure and field types of the Phoenix Diary model.
     *
     * @param string $account
     * @return JsonResponse
     */
    public function fields($account)
    {
        return parent::fields($account);
    }

    /**
     * Upload a CSV file for bulk Phoenix Diary entry processing.
     *
     * Upload a CSV file to create multiple Phoenix Diary entries at once.
     *
     * @param string $account
     * @bodyParam file file required The CSV file to upload (max 1MB). Must be a CSV file.
     * @bodyParam header_mapping array required Array of headers mapping to Phoenix Diary entry fields.
     * @return JsonResponse
     */
    public function upload($account)
    {
        return parent::upload($account);
    }

    /**
     * Get the status of a bulk Phoenix Diary entry upload.
     *
     * Check the progress of a previously submitted bulk upload.
     *
     * @param string $account
     * @return JsonResponse
     */
    public function uploadStatus($account)
    {
        return parent::uploadStatus($account);
    }

    /**
     * Delete a bulk Phoenix Diary entry upload.
     *
     * Remove a pending or processing bulk upload.
     *
     * @param string $account
     * @param int $id
     * @return JsonResponse
     */
    public function deleteUpload($account, $id)
    {
        return parent::deleteUpload($account, $id);
    }

    /**
     * Get available characters for Phoenix Diary.
     *
     * Retrieve a list of all available characters for the Phoenix Diary game.
     *
     * @return JsonResponse
     */
    public function getCharacters()
    {
        /** @var User $user */
        $user = auth()->user();
        if (!$user) {
            return response()->json('Login required.', 403);
        }

        $returnThePhoenixDiaryGame = ThePhoenixDiaryUtilities::getCharacters($user);

        return response()->json($returnThePhoenixDiaryGame);
    }

    /**
     * Create a new Phoenix Diary game.
     *
     * Start a new game session with the selected character.
     *
     * @param Request $request
     * @bodyParam character_id integer required The ID of the character to use in the game. Example: 1
     * @return JsonResponse
     */
    public function createNewGame(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();
        if (!$user) {
            return response()->json('Login required.', 403);
        }

        $data = $request->validate([
            'character_id' => 'required'
        ]);
        $character = TpdCharacter::find($data['character_id']);
        if (!$character) {
            return response()->json('Character not found.', 404);
        }

        $returnThePhoenixDiaryGame = ThePhoenixDiaryUtilities::createNewGame($user, $character);

        return response()->json($returnThePhoenixDiaryGame);
    }
}
