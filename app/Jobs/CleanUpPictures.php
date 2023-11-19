<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Picture;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CleanUpPictures implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $files = Storage::allFiles('orders');
        $orders = Order::query()->whereNotNull('screenshot')->get();
        collect($files)->each(function ($file) use ($orders) {
            if ($orders->doesntContain(fn ($order) => $order->getRawOriginal('screenshot') == $file)) {
                Storage::delete($file);
            }
        });
    }
}
