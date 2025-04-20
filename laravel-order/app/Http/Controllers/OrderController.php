<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\OrderMeta;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;

class OrderController extends Controller
{
    /**
     * 顯示訂單列表
     */
    public function index(): JsonResponse
    {
        $orders = Order::with(['items'])->orderBy('date_created_gmt', 'desc')->paginate(10);
        return Response::json($orders);
    }

    // 建立新訂單
    public function store(Request $request): JsonResponse
    {
        // 驗證請求ai產生
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

        DB::beginTransaction();

        try {
            $now = Carbon::now();
            $order = Order::create([
                'status' => $request->status,
                'currency' => $request->currency,
                'type' => $request->type,
                'tax_amount' => $request->tax_amount ?? 0,
                'total_amount' => $request->total_amount,
                'customer_id' => rand(1, 12000), // 模擬不同的用戶, 確保database有不同的customer_id
                'billing_email' => $request->billing_email,
                'payment_method' => $request->payment_method,
                'payment_method_title' => $request->payment_method_title,
                'transaction_id' => $request->transaction_id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'customer_note' => $request->customer_note,
                'date_created_gmt' => $now->toDateTimeString(),
                'date_updated_gmt' => $now->toDateTimeString(),
            ]);

            // 儲存訂單項目
            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    $orderItem = $order->items()->create([
                        'order_item_name' => $item['order_item_name'],
                        'order_item_type' => $item['order_item_type'],
                    ]);

                    // 儲存訂單項目元數據
                    $orderItem->metas()->create([
                        'meta_key' => '_line_subtotal',
                        'meta_value' => $item['price'] * $item['quantity']
                    ]);

                    $orderItem->metas()->create([
                        'meta_key' => '_line_total',
                        'meta_value' => $item['price'] * $item['quantity']
                    ]);

                    $orderItem->metas()->create([
                        'meta_key' => '_qty',
                        'meta_value' => $item['quantity']
                    ]);

                    $orderItem->metas()->create([
                        'meta_key' => '_line_tax',
                        'meta_value' => $item['tax_amount'] ?? 0
                    ]);

                    // 儲存訂單項目其他元數據
                    if (isset($item['meta']) && is_array($item['meta'])) {
                        foreach ($item['meta'] as $key => $value) {
                            $orderItem->metas()->create([
                                'meta_key' => $key,
                                'meta_value' => $value,
                            ]);
                        }
                    }
                }
            }

            // 儲存訂單元數據
            if ($request->has('meta') && is_array($request->meta)) {
                foreach ($request->meta as $key => $value) {
                    $order->setMeta($key, $value);
                }
            }

            DB::commit();

            return Response::json([
                'code' => 0,
                'message' => '訂單已成功建立',
                'order' => $order->load(['items.metas', 'metas'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::json(['code' => '-1', 'message' => '訂單建立失敗', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * 顯示指定的訂單
     */
    public function show(Order $order): JsonResponse
    {
        return Response::json($order->load(['items.metas', 'metas']));
    }

    /**
     * 更新指定的訂單
     */
    public function update(Request $request, Order $order): JsonResponse
    {
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

        DB::beginTransaction();

        try {
            // 更新訂單基本資訊
            $order->fill($request->all());
            // 更新日期
            if (in_array('date_updated_gmt', $order->getFillable())) {
                $order->setAttribute('date_updated_gmt', Carbon::now()->toDateTimeString());
            }
            $order->save();

            // 如果請求中包含元數據更新
            if ($request->has('meta') && is_array($request->meta)) {
                foreach ($request->meta as $key => $value) {
                    $order->setMeta($key, $value);
                }
            }

            // 更新訂單項目
            if ($request->has('items') && is_array($request->items)) {
                foreach ($request->items as $itemData) {
                    // 如果項目已存在則更新，否則新增
                    if (isset($itemData['id'])) {
                        $orderItem = OrderItem::where('order_item_id', $itemData['id'])
                            ->where('order_id', $order->getKey())
                            ->first();

                        if ($orderItem) {
                            // 更新訂單項目基本資訊
                            $orderItem->update([
                                'order_item_name' => $itemData['order_item_name'] ?? $orderItem->order_item_name,
                                'order_item_type' => $itemData['order_item_type'] ?? $orderItem->order_item_type,
                            ]);

                            // 更新訂單項目元數據
                            if (isset($itemData['price'])) {
                                $price = $itemData['price'];
                                $quantity = $itemData['quantity'] ?? $orderItem->getQuantity();
                                $orderItem->metas()->updateOrCreate(
                                    ['meta_key' => '_line_subtotal'],
                                    ['meta_value' => $price * $quantity]
                                );
                                $orderItem->metas()->updateOrCreate(
                                    ['meta_key' => '_line_total'],
                                    ['meta_value' => $price * $quantity]
                                );
                            }

                            if (isset($itemData['quantity'])) {
                                $quantity = $itemData['quantity'];
                                $price = isset($itemData['price']) ? $itemData['price'] : $orderItem->getPrice();
                                $orderItem->metas()->updateOrCreate(
                                    ['meta_key' => '_qty'],
                                    ['meta_value' => $quantity]
                                );
                                // 更新小計
                                $orderItem->metas()->updateOrCreate(
                                    ['meta_key' => '_line_subtotal'],
                                    ['meta_value' => $price * $quantity]
                                );
                                $orderItem->metas()->updateOrCreate(
                                    ['meta_key' => '_line_total'],
                                    ['meta_value' => $price * $quantity]
                                );
                            }

                            if (isset($itemData['tax_amount'])) {
                                $orderItem->metas()->updateOrCreate(
                                    ['meta_key' => '_line_tax'],
                                    ['meta_value' => $itemData['tax_amount']]
                                );
                            }

                            // 更新其他元數據
                            if (isset($itemData['meta']) && is_array($itemData['meta'])) {
                                foreach ($itemData['meta'] as $key => $value) {
                                    $orderItem->metas()->updateOrCreate(
                                        ['meta_key' => $key],
                                        ['meta_value' => $value]
                                    );
                                }
                            }
                        }
                    } else {
                        // 新增訂單項目
                        $orderItem = $order->items()->create([
                            'order_item_name' => $itemData['order_item_name'],
                            'order_item_type' => $itemData['order_item_type'],
                        ]);

                        // 儲存基本元數據
                        $price = $itemData['price'];
                        $quantity = $itemData['quantity'];
                        $orderItem->metas()->create([
                            'meta_key' => '_line_subtotal',
                            'meta_value' => $price * $quantity
                        ]);

                        $orderItem->metas()->create([
                            'meta_key' => '_line_total',
                            'meta_value' => $price * $quantity
                        ]);

                        $orderItem->metas()->create([
                            'meta_key' => '_qty',
                            'meta_value' => $quantity
                        ]);

                        $orderItem->metas()->create([
                            'meta_key' => '_line_tax',
                            'meta_value' => $itemData['tax_amount'] ?? 0
                        ]);

                        // 儲存其他元數據
                        if (isset($itemData['meta']) && is_array($itemData['meta'])) {
                            foreach ($itemData['meta'] as $key => $value) {
                                $orderItem->metas()->create([
                                    'meta_key' => $key,
                                    'meta_value' => $value,
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();

            return Response::json([
                'code' => 0,
                'message' => '訂單已成功更新',
                'order' => $order->fresh(['items.metas', 'metas'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::json(['code' => '-1', 'message' => '訂單更新失敗', 'error' => $e->getMessage()]);
        }
    }

    public function destroy($orderId)
    {
        try {
            $order = Order::find($orderId);

            if (!$order) {
                return Response::json(['code' => '-1']);
            }

            $order->delete();

            return Response::json(['code' => 0]);
        } catch (\Exception $e) {
            // log 
            return Response::json(['code' => '-1', 'message' => '訂單刪除失敗', 'error' => $e->getMessage()]);
        }
    }
}
