<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseController;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class CategoryController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/categories",
     *     operationId="getCategories",
     *     tags={"Category"},
     *     summary="Get all categories",
     *     description="Get all categories",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index()
    {
        return $this->sendResponse(Category::all(), 'Get categories successfully.');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/categories",
     *     operationId="createCategory",
     *     tags={"Category"},
     *     summary="Create a category",
     *     description="Create a category",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *            mediaType="application/json",
     *            @OA\Schema(
     *               type="object",
     *               required={"name"},
     *               @OA\Property(property="name", type="text", example="")
     *            ),
     *        ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Create category successfully.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $body = $request->all();
            $validated = Validator::make($body, [
                'name' => 'required|min:3'
            ]);

            if ($validated->fails()) {
                return $this->sendError('Validation Error.', $validated->errors(), 400);
            }

            $new_category = Category::create([
                'name' => $body['name']
            ]);

            return $this->sendResponse($new_category, 'Create category successfully.');
        } catch (\Throwable $th) {
            //throw $th;
            Log::error($th);
            return $this->sendError('An error occurred during create category', [], 500);
        }
    }
}
