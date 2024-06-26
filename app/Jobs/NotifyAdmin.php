<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Telegram;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyAdmin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
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
        $admins = User::query()->where('notification_active', true)->whereRelation('roles', 'name', '=', 'admin')->pluck('telegram_chat_id')->filter(fn ($v) => $v != null)->unique();
        if (count($admins) <= 0) return;
        $link = config('app.frontend_url') . '/order/' . $this->orderId;
        $message = "<a href='$link'>Order:$this->orderId</a> received a payment.";
        $admins->each(fn ($admin) => Telegram::sendMessage($admin, $message));
    }
}
