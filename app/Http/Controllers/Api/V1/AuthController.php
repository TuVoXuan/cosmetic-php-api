<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Authentication related operations"
 * )
 */
class AuthController extends BaseController
{
  /**
   * @OA\Post(
   * path="/api/v1/auth/register",
   * operationId="Register",
   * tags={"Auth"},
   * summary="User Register",
   * description="User Register here",
   *     @OA\RequestBody(
   *         @OA\MediaType(
   *            mediaType="application/json",
   *            @OA\Schema(
   *               type="object",
   *               required={"first_name","last_name", "email", "password"},
   *               @OA\Property(property="first_name", type="text", example=""),
   *               @OA\Property(property="last_name", type="text", example=""),
   *               @OA\Property(property="email", type="text", example=""),
   *               @OA\Property(property="password", type="password", example=""),
   *            ),
   *        ),
   *     ),
   *     @OA\Response(
   *         response=201,
   *         description="Login Successfully",
   *         @OA\JsonContent()
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Register Successfully",
   *         @OA\JsonContent()
   *     ),
   *     @OA\Response(response=400, description="Bad request"),
   *     @OA\Response(response=404, description="Resource Not Found"),
   * )
   */
  public function register(Request $request)
  {
    try {
      $body = $request->all();
      $validated = Validator::make($body, [
        'first_name' => 'required|min:3|max:20',
        'last_name' => 'required|min:3|max:20',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6'
      ]);

      if ($validated->fails()) {
        return $this->sendError('Validation Error.', $validated->errors(), 400);
      }

      User::create([
        'first_name' => $body['first_name'],
        'last_name' => $body['last_name'],
        'email' => $body['email'],
        'password' => Hash::make($body['password']),
        'role_id' => 1
      ]);

      return $this->sendResponse('', 'Register successfully.');
    } catch (\Throwable $th) {
      // Log the exception
      Log::error($th);

      // Return an error response
      return $this->sendError('An error occurred during registration.', [], 500);
    }
  }

  /**
   * @OA\Post(
   * path="/api/v1/auth/login",
   * operationId="Login",
   * tags={"Auth"},
   * summary="User Login",
   * description="User Login here",
   *     @OA\RequestBody(
   *         @OA\MediaType(
   *            mediaType="application/json",
   *            @OA\Schema(
   *               type="object",
   *               required={"email", "password"},
   *               @OA\Property(property="email", type="text", example="admin@test.com"),
   *               @OA\Property(property="password", type="password", example="123456"),
   *            ),
   *        ),
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Login Successfully",
   *         @OA\JsonContent()
   *     ),
   *     @OA\Response(response=400, description="Bad request"),
   *     @OA\Response(response=404, description="Resource Not Found"),
   * )
   */
  public function login(Request $request)
  {
    try {
      $body = $request->all();
      $validated = Validator::make($body, [
        'email' => 'required|email',
        'password' => 'required|min:6'
      ]);

      if ($validated->fails()) {
        return $this->sendError('Validation Error.', $validated->errors(), 400);
      }

      $credentials = $request->only(['email', 'password']);
      if (!$token = JWTAuth::attempt($credentials)) {
        return $this->sendError('Invalid email or password', [], 400);
      }

      $user = JWTAuth::user();
      return $this->sendResponse([
        'user' => $user,
        'token' => $token
      ], 'Login successfully.');
    } catch (\Throwable $th) {
      // Log the exception
      Log::error($th);

      // Return an error response
      return $this->sendError('An error occurred during login.', [], 500);
    }
  }
}
