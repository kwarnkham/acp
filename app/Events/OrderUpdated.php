<?php

namespace App\Events;

use App\Models\Order;
use App\Models\Round;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;
    /**
     * Create a new event instance.
     */
    public function __construct(int $orderId)
    {
        $this->order = Order::with(['tickets', 'round' => [
            'orderDetails',
            'ticket',
            'item'
        ]])->find($orderId);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('rounds.' . $this->order->round->id),
            new PrivateChannel('orders.' . $this->order->id)
        ];
    }
}
