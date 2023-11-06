<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatus;
use App\Enums\TicketStatus;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Preference;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            $user = $request->user();
            if ($data['status'] == TicketStatus::BOOKED->value) {
                abort_unless(
                    $user->bookTicket($ticket),
                    ResponseStatus::BAD_REQUEST->value,
                    'Error Booking Ticket'
                );
            } else if ($data['status'] == TicketStatus::PAID->value) {
                abort_unless(
                    $user->payTicket($ticket, $data['screenshot']),
                    ResponseStatus::BAD_REQUEST->value,
                    'Error Paying Ticket'
                );
            } else if ($data['status'] == TicketStatus::CONFIRMED_PAID->value) {
                abort_unless(
                    $user->confirmPaid($ticket),
                    ResponseStatus::BAD_REQUEST->value,
                    'Error Confirm Ticket Payment'
                );
            }
        });

        return response()->json(['ticket' => $ticket->load(['item'])]);
    }
}
