<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\ResponseStatus;
use App\Models\Order;
use App\Models\Round;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        abort_unless($request->exists('round_id'), ResponseStatus::BAD_REQUEST->value, "No Round ID found");
        $round = Round::query()->findOrFail($request->round_id);
        $data = $request->validate([
            'phone' => ['required'],
            'codes' => ['required', 'array'],
            'codes.*' => ['required', 'numeric', 'lt:' . $round->max_tickets],
        ]);

        $codes = $round->orderDetails()->wherePivotIn('code', $data['codes'])->pluck('code');

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
                    'code' => $code,
                    'price' => $round->price_per_ticket
                ]);
            }

            return $order;
        });


        return response()->json(['order' => $order]);
    }

    public function find(Request $request, Order $order)
    {
        return response()->json(['order' => $order->load(['round.item', 'rounds'])]);
    }

    public function pay(Request $request, Order $order)
    {
        abort_unless($order->status == OrderStatus::PENDING->value, ResponseStatus::BAD_REQUEST->value, 'Can only pay a pedning order');
        $data = $request->validate([
            'picture' => ['required', 'image'],
            'note' => ['sometimes']
        ]);
        $user = $request->user();

        DB::transaction(function () use ($order, $data, $user) {
            $path = Storage::putFile('orders', $data['picture']);

            $order->update([
                'status' => $user->is_admin ? OrderStatus::CONFIRMED_PAID->value : OrderStatus::PAID->value,
                'note' => $data['note'],
                'screenshot' => $path
            ]);
        });

        return response()->json(['order' => $order->fresh(['round.item', 'rounds'])]);
    }
}
