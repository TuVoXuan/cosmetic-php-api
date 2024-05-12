<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ProductImageResource;
use App\Http\Resources\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductController extends BaseController
{
	/**
	 * @OA\Get(
	 *     path="/api/v1/admin/products",
	 *     operationId="getProducts",
	 *     tags={"Product"},
	 *     summary="Get all products",
	 *     description="Get all products",
	 *     security={{"bearerAuth":{}}},
	 *     @OA\Response(
	 *         response=200,
	 *         description="Get products successfully",
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
		return $this->sendResponse(Product::all(), 'Get products successfully.');
	}

	/**
	 * @OA\Post(
	 *     path="/api/v1/admin/products",
	 *     operationId="createProduct",
	 *     tags={"Product"},
	 *     summary="Create a product",
	 *     description="Create a brand",
	 *     security={{"bearerAuth":{}}},
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *            mediaType="multipart/form-data",
	 *            @OA\Schema(
	 *               type="object",
	 * 							 required={"name", "category_id", "brand_id", "price", "quantity", "description", "thumbnail", "images[]"},
	 *               @OA\Property(property="name", type="text", example=""),
	 *               @OA\Property(property="category_id", type="text", example=""),
	 *               @OA\Property(property="brand_id", type="text", example=""),
	 *               @OA\Property(property="price", type="text", example=""),
	 *               @OA\Property(property="quantity", type="text", example=""),
	 *               @OA\Property(property="description", type="text", example=""),
	 *               @OA\Property(property="thumbnail", type="file"),
	 *               @OA\Property(property="images[]", type="array", @OA\Items(type="file")),
	 *            ),
	 *        ),
	 *     ),
	 *     @OA\Response(
	 *         response=200,
	 *         description="Create product successfully.",
	 *         @OA\JsonContent()
	 *     ),
	 *     @OA\Response(
	 *         response=401,
	 *         description="Unauthorized"
	 *     )
	 * )
	 */
	public function create(Request $request)
	{
		try {
			$body = $request->all();
			$validated = Validator::make($body, [
				'name' => 'required|string|min:3|max:255',
				'category_id' => 'required|integer',
				'brand_id' => 'required|integer',
				'price' => 'required|integer',
				'quantity' => 'required|integer',
				'description' => 'required',
				'thumbnail' => 'required|image',
				'images' => 'required|array|max:5',
				'images.*' => 'image'
			]);

			if ($validated->fails()) {
				return $this->sendError('Validation Error.', $validated->errors(), Response::HTTP_BAD_REQUEST);
			}

			$category = Category::where('id', $body['category_id'])->first();
			if (!$category) {
				return $this->sendError('Category not found.', [], Response::HTTP_NOT_FOUND);
			};

			$brand = Brand::where('id', $body['brand_id'])->first();
			if (!$brand) {
				return $this->sendError('Brand not found.', [], Response::HTTP_NOT_FOUND);
			};

			$product = Product::create([
				'name' => $body['name'],
				'category_id' => $body['category_id'],
				'brand_id' => $body['brand_id'],
				'price' => $body['price'],
				'quantity' => $body['quantity'],
				'description' => $body['description'],
			]);

			$cloudinaryImage = $request->file('thumbnail')->storeOnCloudinary('hygge-api/products');
			$imgUrl = $cloudinaryImage->getSecurePath();
			$public_id = $cloudinaryImage->getPublicId();

			$thumbnail = ProductImage::create([
				'product_id' => $product->id,
				'url' => $imgUrl,
				'is_thumbnail' => true,
				'public_id' => $public_id
			]);

			$productImgs = [];

			foreach ($request->images as $file) {
				$cloudinaryImage = $file->storeOnCloudinary('hygge-api/products');
				$imgUrl = $cloudinaryImage->getSecurePath();
				$public_id = $cloudinaryImage->getPublicId();

				$img = ProductImage::create([
					'product_id' => $product->id,
					'url' => $imgUrl,
					'public_id' => $public_id
				]);

				$productImgs[] = new ProductImageResource($img);
			}

			$product['thumbnail'] = new ProductImageResource($thumbnail);
			$product['images'] = $productImgs;

			return $this->sendResponse($product, 'Create product successfully.');
		} catch (\Exception $e) {
			Log::error($e);
			return $this->sendError($e->getMessage(), [], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @OA\Post(
	 *     path="/api/v1/admin/products/{id}",
	 *     operationId="updateProduct",
	 *     tags={"Product"},
	 *     summary="Update a product",
	 *     description="Update a product",
	 *     security={{"bearerAuth":{}}},
	 *     @OA\Parameter(
	 *          description="ID of Product",
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
	 * 							 required={"name", "category_id", "brand_id", "price", "quantity", "description"},
	 *               @OA\Property(property="name", type="text", example=""),
	 *               @OA\Property(property="category_id", type="text", example=""),
	 *               @OA\Property(property="brand_id", type="text", example=""),
	 *               @OA\Property(property="price", type="text", example=""),
	 *               @OA\Property(property="quantity", type="text", example=""),
	 *               @OA\Property(property="description", type="text", example=""),
	 *               @OA\Property(property="thumbnail", type="file"),
	 *               @OA\Property(property="delete_img_ids[]", type="array", @OA\Items(type="text", example="")),
	 *               @OA\Property(property="images[]", type="array", @OA\Items(type="file")),
	 *            ),
	 *        ),
	 *     ),
	 *     @OA\Response(
	 *         response=200,
	 *         description="Create product successfully.",
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
			$body = $request->all();
			$validated = Validator::make($body, [
				'name' => 'required|string|min:3|max:255',
				'category_id' => 'required|integer',
				'brand_id' => 'required|integer',
				'price' => 'required|integer',
				'quantity' => 'required|integer',
				'description' => 'required',
				'thumbnail' => 'sometimes|required|image',
				'delete_img_ids' => 'sometimes|required|array',
				'delete_img_ids.*' => 'sometimes|int',
				'images' => 'sometimes|required|array|max:5',
				'images.*' => 'sometimes|image'
			]);

			if ($validated->fails()) {
				return $this->sendError('Validation Error.', $validated->errors(), Response::HTTP_BAD_REQUEST);
			}

			$category = Category::where('id', $body['category_id'])->first();
			if (!$category) {
				return $this->sendError('Category not found.', [], Response::HTTP_NOT_FOUND);
			};

			$brand = Brand::where('id', $body['brand_id'])->first();
			if (!$brand) {
				return $this->sendError('Brand not found.', [], Response::HTTP_NOT_FOUND);
			};

			$product = Product::where('id', $id)->first();
			if (!$product) {
				return $this->sendError('Product not found.', [], Response::HTTP_NOT_FOUND);
			}

			if ($request->has('thumbnail')) {
				$thumbnail = ProductImage::where('product_id', $product->id)->where('is_thumbnail', true)->first();
				if ($thumbnail) {
					Cloudinary::destroy($thumbnail->public_id);
					$thumbnail->delete();
				}

				$cloudinaryImage = $request->file('thumbnail')->storeOnCloudinary('hygge-api/products');
				$img_url = $cloudinaryImage->getSecurePath();
				$public_id = $cloudinaryImage->getPublicId();

				ProductImage::create([
					'product_id' => $product->id,
					'url' => $img_url,
					'is_thumbnail' => true,
					'public_id' => $public_id
				]);
			}

			if ($request->has('delete_img_ids') && count($request->delete_img_ids) > 0) {
				foreach ($request->delete_img_ids as $product_img_id) {
					$product_img = ProductImage::where('id', $product_img_id)->first();
					if ($product_img) {
						Cloudinary::destroy($product_img->public_id);
						$product_img->delete();
					}
				}
			}

			if ($request->has('images') && count($request->file('images')) > 0) {
				foreach ($request->images as $file) {
					$cloudinaryImage = $file->storeOnCloudinary('hygge-api/products');
					$imgUrl = $cloudinaryImage->getSecurePath();
					$public_id = $cloudinaryImage->getPublicId();

					ProductImage::create([
						'product_id' => $product->id,
						'url' => $imgUrl,
						'public_id' => $public_id
					]);
				}
			}

			$updatedProduct = Product::with('thumbnail', 'images')->where('id', $id)->first();

			return $this->sendResponse(ProductResource::make($updatedProduct), 'Update product successfully.');
		} catch (\Exception $e) {
			Log::error($e);
			return $this->sendError($e->getMessage(), [], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @OA\Delete(
	 *     path="/api/v1/admin/products/{id}",
	 *     operationId="deleteProduct",
	 *     tags={"Product"},
	 *     summary="Delete a product",
	 *     description="Delete a product",
	 *     security={{"bearerAuth":{}}},
	 *     @OA\Parameter(
	 *          description="ID of product",
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
	 *         description="Delete product successfully.",
	 *         @OA\JsonContent()
	 *     ),
	 *     @OA\Response(
	 *         response=401,
	 *         description="Unauthorized"
	 *     )
	 * )
	 */
	public function destroy($id){
		try {
			$product = Product::with('thumbnail', 'images')->where('id', $id)->first();

			if(!$product){
				return $this->sendError('Product not found.', [], Response::HTTP_NOT_FOUND);
			}

			Cloudinary::destroy($product->thumbnail->public_id);

			foreach($product->images as $image){
				Cloudinary::destroy($image->public_id);
			}

			$product->delete();
			
			return $this->sendResponse($id, 'Delete product successfully.');
		} catch (\Exception $e) {
			Log::error($e);
			return $this->sendError($e->getMessage(), [], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}
}
