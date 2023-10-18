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
            'max_tickets' => ['required', 'max:1000'],
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
}
