<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * @OA\Info(
 *    title="Hygge Cosmetic API",
 *    version="1.0.0",
 * )
 * @OA\SecuritySchema(
 *  type="http",
 *  securitySchema="bearerAuth",
 *  schema="bearer",
 *  bearerFormat="JWT"
 * )
 */
class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message){
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $result
        ];
        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessage = [], $statusCode = 404){
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if(!empty($errorMessage)){
            $response['data'] = $errorMessage;
        }

        return response()->json($response, $statusCode);
    }
}
