<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatus;
use App\Models\Item;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Storage;

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
            'pictures.*' => ['sometimes', 'required', 'image']
        ]);

        $item = DB::transaction(function () use ($data, $request) {
            $item = Item::create([
                'name' => $data['name'],
                'max_tickets' => $data['max_tickets'],
                'price_per_ticket' => $data['price_per_ticket'],
                'price' => $data['price'],
            ]);

            if ($request->exists('pictures')) {
                foreach ($data['pictures'] as $picture) {
                    $path = Storage::putFile('items', $picture);
                    $item->pictures()->create(['name' => $path]);
                }
            }
            return $item;
        });

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
