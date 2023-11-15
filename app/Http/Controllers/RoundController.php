<?php

namespace App\Http\Controllers;

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

        return response()->json(['round' => $round]);
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

        return response()->json(['round' => $round]);
    }

    public function find(Request $request, Round $round)
    {
        return response()->json(['round' => $round->load(['item', 'orderDetails'])]);
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'item_id' => ['sometimes']
        ]);
        $query = Round::query()->latest('id')->with(['item'])->filter($filters);
        return response()->json(['data' => $query->paginate($request->per_page ?? 10)]);
    }
}
