<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseController;
use App\Models\Brand;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BrandController extends BaseController
{
	/**
	 * @OA\Get(
	 *     path="/api/v1/admin/brands",
	 *     operationId="getBrands",
	 *     tags={"Brand"},
	 *     summary="Get all brands",
	 *     description="Get all brands",
	 *     security={{"bearerAuth":{}}},
	 *     @OA\Response(
	 *         response=200,
	 *         description="Get all brands successfully.",
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
		return $this->sendResponse(Brand::all(), 'Get all brands successfully.');
	}

	/**
	 * @OA\Post(
	 *     path="/api/v1/admin/brands",
	 *     operationId="createBrand",
	 *     tags={"Brand"},
	 *     summary="Create a brand",
	 *     description="Create a brand",
	 *     security={{"bearerAuth":{}}},
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *            mediaType="multipart/form-data",
	 *            @OA\Schema(
	 *               type="object",
	 *               required={"name", "logo"},
	 *               @OA\Property(property="name", type="text", example=""),
	 *               @OA\Property(property="logo", type="file")
	 *            ),
	 *        ),
	 *     ),
	 *     @OA\Response(
	 *         response=200,
	 *         description="Create brand successfully.",
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
				'name' => 'required|min:3',
				'logo' => 'required|image'
			]);

			if ($validated->fails()) {
				return $this->sendError('Validation Error.', [], Response::HTTP_BAD_REQUEST);
			}

			$cloudinaryImage = $request->file('logo')->storeOnCloudinary('hygge-api/brands');
			$imgUrl = $cloudinaryImage->getSecurePath();
			$publicId = $cloudinaryImage->getPublicId();

			$brand = Brand::create([
				'name' => $body['name'],
				'logo' => $imgUrl,
				'public_id' => $publicId
			]);
			return $this->sendResponse($brand, 'Create brand successfully.');
		} catch (\Exception $e) {
			return $this->sendError($e->getMessage(), [], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @OA\Post(
	 *     path="/api/v1/admin/brands/{id}",
	 *     operationId="updateBrand",
	 *     tags={"Brand"},
	 *     summary="Update a brand",
	 *     description="Update a brand",
	 *     security={{"bearerAuth":{}}},
	 *     @OA\Parameter(
	 *          description="ID of Brand",
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
	 *            mediaType="multipart/form-data",
	 *            @OA\Schema(
	 *               type="object",
	 *               @OA\Property(property="name", type="text", example=""),
	 *               @OA\Property(property="logo", type="file")
	 *            ),
	 *        ),
	 *     ),
	 *     @OA\Response(
	 *         response=200,
	 *         description="Update brand successfully.",
	 *         @OA\JsonContent()
	 *     ),
	 *     @OA\Response(
	 *         response=401,
	 *         description="Unauthorized"
	 *     )
	 * )
	 */
	public function update(Request $request, $id)
	{
		try {
			$brand = Brand::where('id', $id)->first();
			if (!$brand) {
				return $this->sendError('Brand not found.', [], Response::HTTP_NOT_FOUND);
			}

			$body = $request->all();
			$validated = Validator::make($body, [
				'name' => 'sometimes|required|min:3',
				'logo' => 'sometimes|required|image'
			]);

			if ($validated->fails()) {
				return $this->sendError('Validation Error.', $validated->errors(), Response::HTTP_BAD_REQUEST);
			}

			if ($request->hasFile('logo')) {
				Cloudinary::destroy($brand->public_id);
				$cloudinaryImage = $request->file('logo')->storeOnCloudinary('hygge-api/brands');
				$imgUrl = $cloudinaryImage->getSecurePath();
				$public_id = $cloudinaryImage->getPublicId();


				$brand->update([
					'logo' => $imgUrl,
					'public_id' => $public_id
				]);
			}

			if ($request->has('name')) {
				$brand->name = $body['name'];
				$brand->save();
			}

			return $this->sendResponse($brand, 'Update brand successfully');
		} catch (\Exception $e) {
			Log::error($e);
			return $this->sendError($e->getMessage(), [], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @OA\Delete(
	 *     path="/api/v1/admin/brands/{id}",
	 *     operationId="deleteBrand",
	 *     tags={"Brand"},
	 *     summary="Delete a brand",
	 *     description="Delete a brand",
	 *     security={{"bearerAuth":{}}},
	 *     @OA\Parameter(
	 *          description="ID of Brand",
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
	 *         description="Delete brand successfully.",
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
			$brand = Brand::where('id', $id)->first();
			if (!$brand) {
				return $this->sendError('Brand not found.', [], Response::HTTP_NOT_FOUND);
			}

			Cloudinary::destroy($brand->public_id);
			$brand->delete();

			return $this->sendResponse($id, 'Delete brand successfully.');
		} catch (\Exception $e) {
			Log::error($e);
			return $this->sendError($e->getMessage(), [], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}
}
