<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Interfaces\OrderServiceInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderService implements OrderServiceInterface
{

    public function getOrders(int $perPage = 10): LengthAwarePaginator
    {
        return Order::with(['items'])->orderBy('date_created_gmt', 'desc')->paginate($perPage);
    }


    public function getOrderDetail(Order $order): Order
    {
        return $order->load(['items.metas', 'metas']);
    }

    public function createOrder(array $orderData): Order
    {
        DB::beginTransaction();

        try {
            $now = Carbon::now();

            // 建立訂單
            $order = Order::create([
                'status' => $orderData['status'],
                'currency' => $orderData['currency'],
                'type' => $orderData['type'],
                'tax_amount' => $orderData['tax_amount'] ?? 0,
                'total_amount' => $orderData['total_amount'],
                'customer_id' => $orderData['customer_id'] ?? rand(1, 12000), // 如果沒提供，則模擬不同的用戶
                'billing_email' => $orderData['billing_email'],
                'payment_method' => $orderData['payment_method'],
                'payment_method_title' => $orderData['payment_method_title'] ?? null,
                'transaction_id' => $orderData['transaction_id'] ?? null,
                'ip_address' => $orderData['ip_address'] ?? null,
                'user_agent' => $orderData['user_agent'] ?? null,
                'customer_note' => $orderData['customer_note'] ?? null,
                'date_created_gmt' => $now->toDateTimeString(),
                'date_updated_gmt' => $now->toDateTimeString(),
            ]);

            if (isset($orderData['items']) && is_array($orderData['items'])) {
                $this->processOrderItems($order, $orderData['items']);
            }

            if (isset($orderData['meta']) && is_array($orderData['meta'])) {
                foreach ($orderData['meta'] as $key => $value) {
                    $order->setMeta($key, $value);
                }
            }

            DB::commit();

            return $order->load(['items.metas', 'metas']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateOrder(Order $order, array $orderData): Order
    {
        DB::beginTransaction();

        try {

            $order->fill($orderData);


            if (in_array('date_updated_gmt', $order->getFillable())) {
                $order->setAttribute('date_updated_gmt', Carbon::now()->toDateTimeString());
            }

            $order->save();

            if (isset($orderData['meta']) && is_array($orderData['meta'])) {
                foreach ($orderData['meta'] as $key => $value) {
                    $order->setMeta($key, $value);
                }
            }

            if (isset($orderData['items']) && is_array($orderData['items'])) {
                $this->updateOrderItems($order, $orderData['items']);
            }

            DB::commit();

            return $order->load(['items.metas', 'metas']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteOrder(int $orderId): bool
    {
        DB::beginTransaction();

        try {
            $order = Order::findOrFail($orderId);
            foreach ($order->items as $item) {
                $item->metas()->delete();
                $item->delete();
            }

            $order->metas()->delete();
            $order->delete();
            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function processOrderItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            $orderItem = $order->items()->create([
                'order_item_name' => $item['order_item_name'],
                'order_item_type' => $item['order_item_type'],
            ]);

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

    private function updateOrderItems(Order $order, array $items): void
    {
        foreach ($items as $itemData) {

            if (isset($itemData['id'])) {
                $orderItem = OrderItem::where('order_item_id', $itemData['id'])
                    ->where('order_id', $order->getKey())
                    ->first();

                if ($orderItem) {

                    $orderItem->update([
                        'order_item_name' => $itemData['order_item_name'] ?? $orderItem->order_item_name,
                        'order_item_type' => $itemData['order_item_type'] ?? $orderItem->order_item_type,
                    ]);


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
                // new item
                $newItem = $order->items()->create([
                    'order_item_name' => $itemData['order_item_name'],
                    'order_item_type' => $itemData['order_item_type'],
                ]);

                $newItem->metas()->create([
                    'meta_key' => '_line_subtotal',
                    'meta_value' => $itemData['price'] * $itemData['quantity']
                ]);

                $newItem->metas()->create([
                    'meta_key' => '_line_total',
                    'meta_value' => $itemData['price'] * $itemData['quantity']
                ]);

                $newItem->metas()->create([
                    'meta_key' => '_qty',
                    'meta_value' => $itemData['quantity']
                ]);

                $newItem->metas()->create([
                    'meta_key' => '_line_tax',
                    'meta_value' => $itemData['tax_amount'] ?? 0
                ]);

                if (isset($itemData['meta']) && is_array($itemData['meta'])) {
                    foreach ($itemData['meta'] as $key => $value) {
                        $newItem->metas()->create([
                            'meta_key' => $key,
                            'meta_value' => $value,
                        ]);
                    }
                }
            }
        }
    }
}
