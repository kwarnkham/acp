<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\ResponseStatus;
use App\Enums\RoundStatus;
use App\Events\OrderUpdated;
use App\Jobs\NotifyAdmin;
use App\Jobs\ProcessExpiredOrder;
use App\Models\Order;
use App\Models\Round;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'round_id' => ['sometimes'],
            'user_id' => ['sometimes'],
            'phone' => ['sometimes'],
            'status' => ['sometimes']
        ]);

        if (!$request->user()->is_admin) $filters['user_id'] = $request->user()->id;

        $query = Order::query()
            ->latest('id')
            ->filter($filters)
            ->with(['round.item', 'user']);

        if ($request->exists('phone')) $query->whereRelation('user', 'name', 'like', '%' . $filters['phone'] . '%');

        return response()->json(['data' => $query->paginate($request->per_page ?? 10)]);
    }

    public function store(Request $request)
    {
        $round = Round::query()->findOrFail($request->round_id);
        $data = $request->validate([
            'round_id' => ['required', 'exists:rounds,id'],
            'phone' => ['required'],
            'codes' => ['required', 'array'],
            'codes.*' => ['required', 'numeric', 'lte:' . $round->max_tickets],
            'name' => ['required']
        ]);

        $codes = $round->orderDetails()
            ->whereNotIn('status', [OrderStatus::EXPIRED->value, OrderStatus::CANCELED->value])
            ->wherePivotIn('code', $data['codes'])
            ->pluck('code');

        abort_if(count($codes) > 0, ResponseStatus::BAD_REQUEST->value, implode(",", $codes->toArray()) . ". Number already sold out.");

        $order = DB::transaction(function () use ($request, $data, $round) {
            $user = User::query()->where('name', $data['phone'])->first();
            if (!$user) $user = User::create([
                'name' => $data['phone'],
                'phone' => $data['phone'],
                'display_name' => $data['name'],
                'password' => $data['phone']
            ]);

            else {
                if (!$request->user()) {
                    abort(ResponseStatus::BAD_REQUEST->value, "Phone number already exists");
                }
                if (!$request->user()->isAdmin)
                    if ($request->user()->phone != $user->phone) abort(ResponseStatus::BAD_REQUEST->value, "Phone number does not match");
            }

            $order = $user->orders()->create([
                'round_id' => $round->id,
                'amount' => count($data['codes']) * $round->price_per_ticket,
                'expires_at' => now()->addMinutes($round->expires_in)
            ]);

            foreach ($data['codes'] as $code) {
                $order->tickets()->attach($round->id, [
                    'code' => $code,
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
        return response()->json(['order' => $order->load(['round.item', 'tickets', 'user'])]);
    }

    public function pay(Request $request, Order $order)
    {
        abort_unless(in_array($order->status, [
            OrderStatus::PENDING->value, OrderStatus::PAID->value
        ]), ResponseStatus::BAD_REQUEST->value, 'Can only pay a pedning or paid order');

        abort_unless($order->round->status == RoundStatus::ONGOING->value, ResponseStatus::BAD_REQUEST->value, 'Round has been already settled');

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
                'note' => $data['note'] ?? '',
                'screenshot' => $path
            ]);

            NotifyAdmin::dispatch($order->id);
        });

        return response()->json(['order' => $order->fresh(['round.item', 'tickets', 'user'])]);
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

        return response()->json(['order' => $order->fresh(['round.item', 'tickets', 'user'])]);
    }

    public function confirm(Request $request, Order $order)
    {
        abort_unless($request->user()->is_admin, ResponseStatus::UNAUTHORIZED->value);

        abort_unless(in_array($order->status, [
            OrderStatus::PAID->value
        ]), ResponseStatus::BAD_REQUEST->value, 'Can only confirm a paid order');

        $order->update([
            'status' => OrderStatus::CONFIRMED_PAID->value,
        ]);

        return response()->json(['order' => $order->fresh(['round.item', 'tickets', 'user'])]);
    }
}
