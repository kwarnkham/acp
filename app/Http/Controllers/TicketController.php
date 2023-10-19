<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatus;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Http\Request;
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

    public function update(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(TicketStatus::statuses())]
        ]);

        abort_unless(
            $data['status'] == TicketStatus::BOOKED->value && $ticket->status != TicketStatus::AVAILABLE->value,
            ResponseStatus::BAD_REQUEST->value,
            'Can only book an available ticket'
        );

        abort_unless(
            $data['status'] == TicketStatus::PAID->value && $ticket->status != TicketStatus::BOOKED->value,
            ResponseStatus::BAD_REQUEST->value,
            'Can only pay a booked ticket'
        );

        abort_unless(
            $data['status'] == TicketStatus::CONFIRMED_PAID->value && $ticket->status != TicketStatus::PAID->value,
            ResponseStatus::BAD_REQUEST->value,
            'Can only confirm payment for a paid ticket'
        );

        $ticket->update(['status' => $data['status']]);

        return response()->json(['ticket' => $ticket->load(['item'])]);
    }
}
