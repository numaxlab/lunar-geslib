<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Http\Controllers\Api;

use Lunar\Models\Order;
use NumaxLab\Lunar\Geslib\Http\Resources\Api\Geslib\OrderLineResource;
use NumaxLab\Lunar\Geslib\Http\Resources\Api\Geslib\OrderResource;
use NumaxLab\Lunar\Geslib\Http\Resources\Api\Geslib\PendingOrderResource;

class OrderController
{
    public function indexPending()
    {
        $orders = Order::where('is_geslib', true)
            ->whereIn('status', ['payment-received', 'dispatched'])
            ->whereNotNull('placed_at')
            ->orderBy('placed_at', 'desc')
            ->with(['customer'])
            ->get();

        if ($orders->isEmpty()) {
            return response()->xml([], 200, [], 'pedidosPendientes');
        }

        $pendingOrders = [
            'glmcpedcli' => PendingOrderResource::collection($orders)->resolve(),
        ];

        return response()->xml($pendingOrders, 200, [], 'pedidosPendientes');
    }

    public function show($reference)
    {
        $order = Order::where('reference', $reference)
            ->where('is_geslib', true)
            ->whereNotNull('placed_at')
            ->with([
                'customer',
                'shippingAddress',
                'shippingAddress.country',
                'billingAddress',
                'billingAddress.country',
                'productLines',
                'productLines.purchasable',
                'productLines.order',
            ])
            ->first();

        if (! $order) {
            return response()->xml([], 200, [], 'getPedido');
        }

        $response = [
            'glmcpedcli' => new OrderResource($order)->resolve(),
            'glmlpedcli' => OrderLineResource::collection($order->productLines)
                ->map(fn($line, $index) => $line->additional(['index' => $index + 1])->resolve())
                ->toArray(),
        ];

        return response()->xml($response, 200, [], 'getPedido');
    }

    public function sync($reference)
    {
        $order = [];

        return response()->xml($order, 200, [], 'getPedido');
    }
}
