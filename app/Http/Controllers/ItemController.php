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
            'name' => ['required'],
            'max_tickets' => ['required', 'max:1000', 'numeric'],
            'price_per_ticket' => ['required', 'min:0', 'numeric'],
            'price' => ['required', 'min:0', 'numeric'],
            'pictures' => ['sometimes', 'required', 'array'],
            'pictures.*' => ['sometimes', 'required', 'image'],
            'description' => ['sometimes'],
            'note' => ['sometimes']
        ]);

        $item = DB::transaction(function () use ($data, $request) {
            $item = Item::create([
                'name' => $data['name'],
                'max_tickets' => $data['max_tickets'],
                'price_per_ticket' => $data['price_per_ticket'],
                'price' => $data['price'],
                'description' => $data['description'],
                'note' => $data['note']
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
        return response()->json(['item' => $item]);
    }

    public function update(Request $request, Item $item)
    {
        $data = $request->validate([
            'name' => ['required'],
            'price_per_ticket' => ['required', 'min:0', 'numeric'],
            'price' => ['required', 'min:0', 'numeric'],
            'pictures' => ['sometimes', 'required', 'array'],
            'pictures.*' => ['sometimes', 'required', 'image'],
            'description' => ['sometimes'],
            'note' => ['sometimes']
        ]);

        DB::transaction(function () use ($item, $data, $request) {
            $item->update($data);
            if ($request->exists('pictures'))
                $item->storePictures($data['pictures']);
        });


        return response()->json(['item' => $item]);
    }

    public function result(Request $request, Item $item)
    {
        $data = $request->validate([
            'ticket_id' => [
                'required',
                Rule::exists('tickets', 'id')->where(
                    fn (Builder $query) => $query->where('item_id', $item->id)
                )
            ],
        ]);

        $item->update([
            'ticket_id' => $data['ticket_id']
        ]);

        return response()->json(['item' => $item]);
    }
}
