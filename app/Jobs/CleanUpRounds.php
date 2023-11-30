<?php

namespace App\Jobs;

use App\Enums\RoundStatus;
use App\Models\Round;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanUpRounds implements ShouldQueue
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
        $query = Round::query()->where([
            ['status', '=', RoundStatus::SETTLED->value],
            ['updated_at', '<', now()->subDays(7)],
        ]);

        $rounds = $query->with(['orders'])->get();

        $query->updateQuietly(['ticket_id' => null]);

        $rounds->each(function ($round) {
            $round->orderDetails()->detach();
            $round->paymentMethods()->detach();
            $round->orders()->delete();
            $round->delete();
        });
    }
}
