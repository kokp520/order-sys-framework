<?php

namespace App\Services\Interfaces;

use App\Models\Order;

interface OrderServiceInterface
{
    public function getOrders(int $perPage = 10);
    public function getOrderDetail(Order $order): Order;
    public function createOrder(array $orderData): Order;
    public function updateOrder(Order $order, array $orderData): Order;
    public function deleteOrder(int $orderId): bool;
} 