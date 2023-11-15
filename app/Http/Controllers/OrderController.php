<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\ResponseStatus;
use App\Jobs\ProcessExpiredOrder;
use App\Models\Order;
use App\Models\Round;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'round_id' => ['sometimes'],
            'user_id' => ['sometimes']
        ]);
        $query = Order::query()->latest('id')->filter($filters)->with(['round.item', 'user']);
        return response()->json(['data' => $query->paginate($request->per_page ?? 10)]);
    }

    public function store(Request $request)
    {
        abort_unless($request->exists('round_id'), ResponseStatus::BAD_REQUEST->value, "No Round ID found");
        $round = Round::query()->findOrFail($request->round_id);
        $data = $request->validate([
            'phone' => ['required'],
            'codes' => ['required', 'array'],
            'codes.*' => ['required', 'numeric', 'lte:' . $round->max_tickets],
        ]);

        $codes = array_map(fn ($val) => ($val - 1), $data['codes']);

        Log::info($codes);

        $codes = $round->orderDetails()
            ->whereNotIn('status', [OrderStatus::EXPIRED->value, OrderStatus::CANCELED->value])
            ->wherePivotIn('code', $codes)
            ->pluck('code');

        abort_if(count($codes) > 0, ResponseStatus::BAD_REQUEST->value, implode(",", $codes->toArray()) . ". Number already sold out.");

        $order = DB::transaction(function () use ($request, $data, $round) {
            $user = $request->user();

            if ($user->is_admin) {
                $user = User::query()->createOrFirst([
                    'name' => $data['phone'],
                    'phone' => $data['phone'],
                ]);

                $user->password = bcrypt($data['phone']);
                $user->save();
            }

            $order = $user->orders()->create([
                'round_id' => $round->id,
                'amount' => count($data['codes']) * $round->price_per_ticket,
                'expires_at' => now()->addMinutes($round->expires_in)
            ]);

            foreach ($data['codes'] as $code) {
                $order->rounds()->attach($round->id, [
                    'code' => $code - 1,
                    'price' => $round->price_per_ticket
                ]);
            }

            ProcessExpiredOrder::dispatch($order->id)->delay(now()->addMinutes($round->expires_in));

            return $order;
        });


        return response()->json(['order' => $order]);
    }

    public function find(Request $request, Order $order)
    {
        return response()->json(['order' => $order->load(['round.item', 'rounds', 'user'])]);
    }

    public function pay(Request $request, Order $order)
    {
        abort_unless(in_array($order->status, [
            OrderStatus::PENDING->value, OrderStatus::PAID->value
        ]), ResponseStatus::BAD_REQUEST->value, 'Can only pay a pedning or paid order');
        $data = $request->validate([
            'picture' => ['required', 'image'],
            'note' => ['sometimes']
        ]);
        $user = $request->user();

        DB::transaction(function () use ($order, $data, $user) {
            $path = $order->getRawOriginal('screenshot');
            if ($path && Storage::exists($path)) Storage::delete($path);
            $path = Storage::putFile('orders', $data['picture']);

            $order->update([
                'status' => $user->is_admin ? OrderStatus::CONFIRMED_PAID->value : OrderStatus::PAID->value,
                'note' => $data['note'],
                'screenshot' => $path
            ]);
        });

        return response()->json(['order' => $order->fresh(['round.item', 'rounds'])]);
    }

    public function cancel(Request $request, Order $order)
    {
        abort_unless($request->user()->is_admin, ResponseStatus::UNAUTHORIZED->value);

        abort_unless(in_array($order->status, [
            OrderStatus::PENDING->value,
            OrderStatus::PAID->value
        ]), ResponseStatus::BAD_REQUEST->value, 'Can only cancel a pending or paid order');

        $order->update([
            'status' => OrderStatus::CANCELED->value,
        ]);

        return response()->json(['order' => $order->fresh(['round.item', 'rounds'])]);
    }
}
