<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Interfaces\OrderServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index(OrderServiceInterface $orderService): JsonResponse
    {
        $orders = $orderService->getOrders();
        return Response::json($orders);
    }

    public function store(Request $request, OrderServiceInterface $orderService): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|max:20',
            'currency' => 'required|string|max:10',
            'type' => 'required|string|max:20',
            'customer_id' => 'nullable|integer',
            'billing_email' => 'required|email|max:320',
            'payment_method' => 'required|string|max:100',
            'total_amount' => 'required|numeric',
            'tax_amount' => 'nullable|numeric',
            'items' => 'required|array|min:1',
            'items.*.order_item_name' => 'required|string',
            'items.*.order_item_type' => 'required|string',
            'items.*.price' => 'required|numeric',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.tax_amount' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->errors()], 422);
        }

        try {
            $orderData = $request->all();
            $orderData['ip_address'] = $request->ip();
            $orderData['user_agent'] = $request->userAgent();
            
            $order = $orderService->createOrder($orderData);
            
            return Response::json([
                'code' => 0,
                'message' => '訂單已成功建立',
                'order' => $order
            ], 201);
        } catch (\Exception $e) {
            return Response::json(['code' => '-1', 'message' => '訂單建立失敗', 'error' => $e->getMessage()], 500);
        }
    }
    
    public function show(Request $request, $orderId, OrderServiceInterface $orderService): JsonResponse
    {
        try {
            $order = Order::findOrFail($orderId);
            $orderDetail = $orderService->getOrderDetail($order);
            return Response::json($orderDetail);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json(['code' => '-1', 'message' => '訂單不存在'], 404);
        } catch (\Exception $e) {
            return Response::json(['code' => '-1', 'message' => '獲取訂單失敗', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $orderId, OrderServiceInterface $orderService): JsonResponse
    {
        try {
            $order = Order::findOrFail($orderId);
            
            $validator = Validator::make($request->all(), [
                'status' => 'nullable|string|max:20',
                'currency' => 'nullable|string|max:10',
                'type' => 'nullable|string|max:20',
                'customer_id' => 'nullable|integer',
                'billing_email' => 'nullable|email|max:320',
                'payment_method' => 'nullable|string|max:100',
                'total_amount' => 'nullable|numeric',
                'tax_amount' => 'nullable|numeric',
                'items' => 'nullable|array',
                'items.*.id' => 'nullable|integer',
                'items.*.order_item_name' => 'nullable|string',
                'items.*.order_item_type' => 'nullable|string',
                'items.*.price' => 'nullable|numeric',
                'items.*.quantity' => 'nullable|integer|min:1',
                'items.*.tax_amount' => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                return Response::json(['code' => '-1', 'message' => '訂單更新失敗', 'errors' => $validator->errors()], 422);
            }

            $updatedOrder = $orderService->updateOrder($order, $request->all());
            
            return Response::json([
                'code' => 0,
                'message' => '訂單已成功更新',
                'order' => $updatedOrder
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json(['code' => '-1', 'message' => '訂單不存在'], 404);
        } catch (\Exception $e) {
            return Response::json(['code' => '-1', 'message' => '訂單更新失敗', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($orderId, OrderServiceInterface $orderService)
    {
        try {
            $order = Order::findOrFail($orderId);
            $orderService->deleteOrder($orderId);
            
            return Response::json([
                'code' => 0,
                'message' => '訂單已成功刪除'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::json(['code' => '-1', 'message' => '訂單不存在'], 404);
        } catch (\Exception $e) {
            return Response::json(['code' => '-1', 'message' => '訂單刪除失敗', 'error' => $e->getMessage()], 500);
        }
    }
}
