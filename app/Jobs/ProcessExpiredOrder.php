<?php

namespace App\Jobs;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessExpiredOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**$
     * Create a new job instance.
     */
    public function __construct(public int $orderId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Order::query()->where([
            ['id', '=', $this->orderId],
            ['status', '=', OrderStatus::PENDING->value],
        ])->update(['status' => OrderStatus::EXPIRED->value]);
    }
}
