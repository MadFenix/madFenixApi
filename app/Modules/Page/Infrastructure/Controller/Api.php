<?php
namespace App\Modules\Page\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Page\Domain\Page;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Page management
 *
 * APIs for managing pages
 */
class Api extends ResourceController
{
    /**
     * Display a listing of pages.
     *
     * Get a paginated list of all pages.
     *
     * @param Request $request
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter pages by name. Example: "Home Page"
     * @bodyParam sorting string Sort pages by column and direction (column:direction). Example: "created_at:desc"
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * Store a newly created page.
     *
     * Create a new page with the provided data.
     *
     * @bodyParam name string The name of the page. Example: "Home Page"
     * @bodyParam content string The content of the page. Example: "<h1>Welcome to our website</h1>"
     * @bodyParam seo_title string The SEO title of the page. Example: "Home | My Website"
     * @bodyParam seo_description string The SEO description of the page. Example: "Welcome to my website, the best place for..."
     * @bodyParam seo_image string The SEO image URL of the page. Example: "https://example.com/image.jpg"
     * @return JsonResponse
     */
    public function store()
    {
        return parent::store();
    }

    /**
     * Display the specified page.
     *
     * Get details of a specific page by ID.
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
     * Update the specified page.
     *
     * Update an existing page with the provided data.
     *
     * @param string $account
     * @param int $id
     * @bodyParam name string The name of the page. Example: "Home Page"
     * @bodyParam content string The content of the page. Example: "<h1>Welcome to our website</h1>"
     * @bodyParam seo_title string The SEO title of the page. Example: "Home | My Website"
     * @bodyParam seo_description string The SEO description of the page. Example: "Welcome to my website, the best place for..."
     * @bodyParam seo_image string The SEO image URL of the page. Example: "https://example.com/image.jpg"
     * @return JsonResponse
     */
    public function update($account, $id)
    {
        return parent::update($account, $id);
    }

    /**
     * Remove the specified page.
     *
     * Delete a page by ID.
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
     * Download pages data.
     *
     * Download pages data in CSV format.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function download(Request $request)
    {
        return parent::download($request);
    }

    /**
     * Get fields for pages.
     *
     * Get the available fields for pages.
     *
     * @param string $account
     * @return JsonResponse
     */
    public function fields($account)
    {
        return parent::fields($account);
    }

    /**
     * Upload pages data.
     *
     * Upload pages data in CSV format.
     *
     * @param string $account
     * @return JsonResponse
     */
    public function upload($account)
    {
        return parent::upload($account);
    }

    /**
     * Get upload status.
     *
     * Get the status of a pages data upload.
     *
     * @param string $account
     * @return JsonResponse
     */
    public function uploadStatus($account)
    {
        return parent::uploadStatus($account);
    }

    /**
     * Delete an upload.
     *
     * Delete a pages data upload by ID.
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
     * Get the model name.
     *
     * @return string
     */
    public function getModelName(): string
    {
        return 'page';
    }

    /**
     * Get the parent identificator.
     *
     * @return string
     */
    public function getParentIdentificator(): string
    {
        return '';
    }

    /**
     * Get the model class.
     *
     * @return string
     */
    public function getModelClass(): string
    {
        return Page::class;
    }

    /**
     * Get the transformer class.
     *
     * @return string
     */
    public function getTransformerClass(): string
    {
        return \App\Modules\Page\Transformers\Page::class;
    }

    /**
     * Get page by name.
     *
     * Retrieve a page by its name.
     *
     * @param string $account
     * @param string $name
     * @return JsonResponse
     */
    public function getByName($account, $name)
    {
        try {
            $page = Page::where('name', $name)->first();

            if (!$page) {
                return response()->json([
                    'message' => 'Page not found',
                    'data' => null
                ], 404);
            }

            $transformer = $this->getTransformerClass();

            return response()->json([
                'message' => 'Page retrieved successfully',
                'data' => new $transformer($page)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving page',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
