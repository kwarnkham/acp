<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{

    public function index(Request $request)
    {
        $filters = $request->validate([
            'item_id' => ['required', Rule::exists('items', 'id')],
            'status' => ['sometimes', 'required'],
        ]);

        $query = Ticket::query()->filter($filters);

        return response()->json(['data' => $query->paginate($request->per_page ?? 10)]);
    }

    public function find(Request $request, Ticket $ticket)
    {
        return response()->json(['ticket' => $ticket->load(['item'])]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $ticket) {
            if ($data['status'] == TicketStatus::BOOKED->value) {
                $user = $request->user();
                $user->tickets()->attach($ticket, ['expires_at' => now()->addMinutes(30)]);
            }

            $ticket->update(['status' => $data['status']]);
        });

        return response()->json(['ticket' => $ticket->load(['item'])]);
    }
}
