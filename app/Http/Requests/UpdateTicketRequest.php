<?php

namespace App\Http\Requests;

use App\Enums\ResponseStatus;
use App\Enums\TicketStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(TicketStatus::statuses())]
        ];
    }

    public function after(): array
    {
        return [
            function () {
                $this->validateStatus();
            }
        ];
    }

    public function validateStatus()
    {
        $data = $this->validated();
        $ticket = $this->ticket;

        if ($data['status'] == TicketStatus::BOOKED->value)
            abort_unless(
                $ticket->status == TicketStatus::AVAILABLE->value,
                ResponseStatus::BAD_REQUEST->value,
                'Can only book an available ticket'
            );

        else if ($data['status'] == TicketStatus::PAID->value)
            abort_unless(
                $ticket->status == TicketStatus::BOOKED->value,
                ResponseStatus::BAD_REQUEST->value,
                'Can only pay a booked ticket'
            );

        else if ($data['status'] == TicketStatus::CONFIRMED_PAID->value)
            abort_unless(
                $ticket->status == TicketStatus::PAID->value,
                ResponseStatus::BAD_REQUEST->value,
                'Can only confirm payment for a paid ticket'
            );
    }
}
