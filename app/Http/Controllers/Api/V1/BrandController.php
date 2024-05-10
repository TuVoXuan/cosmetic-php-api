<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseController;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
    public function index(){
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
    public function store(Request $request){
        try {
            $body = $request->all();
            $validated = Validator::make($body, [
                'name' => 'required|min:3',
                'logo' => 'required|image'
            ]);

            if($validated->fails()){
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
}
