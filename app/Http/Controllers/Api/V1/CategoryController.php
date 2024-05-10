<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseController;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


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

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/categories/{id}",
     *     operationId="deleteCategory",
     *     tags={"Category"},
     *     summary="Delete a category",
     *     description="Delete a category",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *          description="ID of Category",
     *          in="path",
     *          name="id",
     *          required=true,
     *          example="1",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Delete category successfully.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $category = Category::where('id', $id)->first();

            if($category){
                $category->delete();
                return $this->sendResponse($category['id'], 'Delete category successfully.');
            }

            return $this->sendError('Category not found',[], 404);
        } catch (\Throwable $th) {
            //throw $th;
            Log::error($th);
            return $this->sendError('An error occurred during delete category', [], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/categories/{id}",
     *     operationId="updateCategory",
     *     tags={"Category"},
     *     summary="Update a category",
     *     description="Update a category",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *          description="ID of Category",
     *          in="path",
     *          name="id",
     *          required=true,
     *          example="1",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *     ),
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
     *         description="Update category successfully.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function update(Request $request, $id){
        try {
            $category = Category::where('id', $id)->first();

            if(!$category){
                return $this->sendError('Category not found.', [], 404);
            }

            $body = $request->all();
            $validated = Validator::make($body, [
                'name' => 'required|min:3'
            ]);

            if($validated->fails()){
                return $this->sendError('Validation error.', $validated->errors(), 400);
            }

            $category->name = $body['name'];
            $category->save();
            return $this->sendResponse($category, 'Update category successfully.');
        } catch (\Throwable $th) {
            //throw $th;
            Log::error($th);
            return $this->sendError('An error occurred during update category', [], 500);
        }
    }
}
