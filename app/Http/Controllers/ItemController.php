<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatus;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required'],
            'max_tickets' => ['required', 'max:1000', 'numeric'],
            'price_per_ticket' => ['required', 'min:0', 'numeric'],
            'price' => ['required', 'min:0', 'numeric']
        ]);

        $item = Item::create($data);

        return response()->json(['item' => $item->fresh()], ResponseStatus::CREATED->value);
    }

    public function index(Request $request)
    {
        return response()->json(['data' => Item::query()->paginate()]);
    }

    public function find(Request $request, Item $item)
    {
        return response()->json(['item' => $item]);
    }

    public function update(Request $request, Item $item)
    {
        $data = $request->validate([
            'name' => ['required'],
            'price' => ['required', 'min:0', 'numeric']
        ]);
        $item->update($data);

        return response()->json(['item' => $item]);
    }
}
