<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatus;
use App\Models\Item;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'unique:items'],
            'description' => ['sometimes'],
            'pictures' => ['sometimes', 'required', 'array'],
            'pictures.*' => ['sometimes', 'required', 'image'],
        ]);

        $item = DB::transaction(function () use ($data, $request) {
            $item = Item::create([
                'name' => $data['name'],
                'description' => $data['description'],
            ]);

            if ($request->exists('pictures'))
                $item->storePictures($data['pictures']);

            return $item;
        });

        return response()->json(['item' => $item->fresh()], ResponseStatus::CREATED->value);
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'name' => ['sometimes']
        ]);
        $query = Item::query()->latest('id')->filter($filters);
        return response()->json(['data' => $query->paginate($request->per_page ?? 10)]);
    }

    public function find(Request $request, Item $item)
    {
        return response()->json(['item' => $item->load(['latestRound.orders', 'latestRound.orderDetails'])]);
    }

    public function update(Request $request, Item $item)
    {
        $data = $request->validate([
            'name' => ['required'],
            'pictures' => ['sometimes', 'required', 'array'],
            'pictures.*' => ['sometimes', 'required', 'image'],
            'description' => ['sometimes'],

        ]);

        DB::transaction(function () use ($item, $data, $request) {
            $item->update([
                'name' => $data['name'],
                'description' => $data['description']
            ]);

            if ($request->exists('pictures'))
                $item->storePictures($data['pictures']);
        });

        return response()->json(['item' => $item]);
    }
}
