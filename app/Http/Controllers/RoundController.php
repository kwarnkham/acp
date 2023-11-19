<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\ResponseStatus;
use App\Enums\RoundStatus;
use App\Events\RoundUpdated;
use App\Models\Round;
use Illuminate\Http\Request;

class RoundController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'max_tickets' => ['required', 'max:1000', 'numeric'],
            'price_per_ticket' => ['required', 'min:0', 'numeric'],
            'price' => ['required', 'min:0', 'numeric'],
            'expires_in' => ['sometimes', 'numeric'],
            'note' => ['sometimes'],
            'item_id' => ['required', 'exists:items,id']
        ]);

        $round = Round::create($data);

        return response()->json(['round' => $round->load(['item', 'orderDetails', 'ticket'])]);
    }

    public function update(Request $request, Round $round)
    {
        $data = $request->validate([
            'price_per_ticket' => ['required', 'min:0', 'numeric'],
            'price' => ['required', 'min:0', 'numeric'],
            'note' => ['sometimes'],
            'expires_in' => ['required', 'numeric'],
        ]);

        $round->update([
            'price_per_ticket' => $data['price_per_ticket'],
            'price' => $data['price'],
            'note' => $data['note'],
            'expires_in' => $data['expires_in'],
        ]);

        return response()->json(['round' => $round->load(['item', 'orderDetails', 'ticket'])]);
    }

    public function settle(Request $request, Round $round)
    {
        $data = $request->validate([
            'code' => ['required', 'numeric', 'gte:0', 'lt:' . $round->max_tickets]
        ]);

        abort_if(
            $round->orders()
                ->whereIn('status', [OrderStatus::PENDING->value, OrderStatus::PAID->value])
                ->exists(),
            ResponseStatus::BAD_REQUEST->value,
            'There are still unfinished orders'
        );

        $order = $round
            ->orderDetails()
            ->where('status', OrderStatus::CONFIRMED_PAID->value)
            ->wherePivot('code', $data['code'])
            ->get();

        abort_if(count($order) > 1, ResponseStatus::BAD_REQUEST->value, 'Cannot determine the order');


        $round->update([
            'ticket_id' => count($order) == 1 ? $order->first()->pivot->id : null,
            'status' => RoundStatus::SETTLED->value,
            'code' => $data['code']
        ]);

        RoundUpdated::dispatch($round->id);

        return response()->json(['round' => $round->load(['item', 'orderDetails', 'ticket'])]);
    }

    public function find(Request $request, Round $round)
    {
        return response()->json(['round' => $round->load(['item', 'orderDetails', 'ticket'])]);
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'item_id' => ['sometimes'],
            'status' => ['sometimes']
        ]);
        $query = Round::query()->latest('id')->with(['item', 'ticket'])->filter($filters);
        return response()->json(['data' => $query->paginate($request->per_page ?? 10)]);
    }
}
