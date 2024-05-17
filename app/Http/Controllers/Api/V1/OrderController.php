<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseController;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderController extends BaseController
{
	public function index(Request $request){
		try {
			$limit = $request->query('limit');
			$page = $request->query('page');
			$from_date = $request->query('from_date');
			$end_date = $request->query('end_date');
			$sort = $request->query('sort');
			$sort_direction = $request->query('sort_direction', 'asc');

			$query = Order::query();

			if($from_date && $end_date){
				$query->whereBetween('order_date', [
					Carbon::parse($from_date)->startOfDay(),
					Carbon::parse($end_date)->endOfDay()
				]);
			}

			if($sort){
				$query->orderBy($sort, $sort_direction);
			}

			$total_records = $query->count();
			$query->with('orderItems', 'user')->limit($limit)->offset($page * $limit);

			$response = [
				'total' => $total_records,
				'data' => OrderResource::collection($query->get())
			];

			return $this->sendResponse($response, 'Get orders successfully.');
		} catch (\Exception $e) {
			Log::error($e);
			return $this->sendError($e->getMessage(), [], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @OA\Post(
	 *     path="/api/v1/admin/orders",
	 *     operationId="createOrder",
	 *     tags={"Order"},
	 *     summary="Create a order",
	 *     description="Create a order",
	 *     security={{"bearerAuth":{}}},
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *            mediaType="application/json",
	 *            @OA\Schema(
	 *               type="object",
	 * 							 required={"cart_items"},
	 *               @OA\Property(property="cart_items", type="array", @OA\Items(
	 * 								type="object",
	 * 								@OA\Property(property="product_id", type="text", example=""),
	 * 								@OA\Property(property="quantity", type="text", example=""),
	 * 							 )),
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
				'cart_items' => 'required|array',
				'cart_items.*.product_id' => 'required|integer|min:1|exists:products,id',
				'cart_items.*.quantity' => 'required|integer|min:1',
			]);

			if ($validated->fails()) {
				return $this->sendError('Validation Error.', $validated->errors(), Response::HTTP_BAD_REQUEST);
			}

			$hasEnoughStock = true;
			$orderItems = [];
			$total_amount = 0;

			foreach ($body['cart_items'] as $cart_item) {
				$product = Product::find($cart_item['product_id']);
				if ($product && $cart_item['quantity'] <= $product->quantity) {
					$orderItems[] = [
						'product_id' => $product->id,
						'quantity' => $cart_item['quantity'],
						'unit_price' => $product->price,
					];
					$total_amount += $product->price * $cart_item['quantity'];
				} else {
					$hasEnoughStock = false;
					break;
				}
			}

			if ($hasEnoughStock) {
				$order = Order::create([
					'user_id' => auth()->user()->id,
					'total_amount' => $total_amount,
					'order_date' => now(),
				]);

				$storedOrderItems = [];

				foreach ($orderItems as $orderItem) {
					$product = Product::find($orderItem['product_id']);
					$product->update(['quantity' => $product->quantity - $orderItem['quantity']]);
					
					$orderItem['order_id'] = $order->id;
					$storedOrderItems[] = OrderItem::create($orderItem);
				}

				$order['order_items'] = $storedOrderItems;

				return $this->sendResponse($order, 'Create order successfully.');
			} else {
				return $this->sendError('Insufficient product quantity in stock.', [], Response::HTTP_BAD_REQUEST);
			}
		} catch (\Exception $e) {
			Log::error($e);
			return $this->sendError($e->getMessage(), [], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}
}
