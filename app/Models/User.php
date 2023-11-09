<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = ['id'];

    protected $hidden = ['password'];

    protected $appends = ['is_admin'];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function isAdmin(): Attribute
    {
        return Attribute::get(fn () => $this->roles()->where('name', 'admin')->exists());
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles->contains(fn ($role) => $role->name == $roleName);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function tickets()
    {
        return $this->belongsToMany(Ticket::class)->withTimestamps()->withPivot(['expires_at', 'screenshot', 'id']);
    }

    public function bookTicket(Ticket $ticket): bool
    {
        $this->tickets()->attach($ticket, ['expires_at' => now()->addMinutes(Preference::first()->ticket_expiration)]);
        return $ticket->update(['status' => TicketStatus::BOOKED->value]);
    }

    public function payTicket(Ticket $ticket, UploadedFile $screenshot): bool
    {
        $userTicket = $this->tickets()
            ->latest('id')
            ->wherePivot('expires_at', '>', now())
            ->wherePivot('ticket_id', $ticket->id)
            ->first();
        if ($userTicket == null) return false;

        $path = Storage::putFileAs(
            'ticket_payments',
            $screenshot,
            $userTicket->pivot->id . '__' . $userTicket->pivot->user_id . '__' . $userTicket->pivot->ticket_id . '.' . $screenshot->getClientOriginalExtension()
        );

        $this->tickets()->updateExistingPivot($ticket->id, ['screenshot' => $path]);
        return $ticket->update(['status' => TicketStatus::PAID->value]);
    }

    public function confirmPaid(Ticket $ticket)
    {
        $this->tickets()->updateExistingPivot($ticket->id, ['expires_at' => null]);
        return $ticket->update(['status' => TicketStatus::CONFIRMED_PAID->value, 'user_id' => $this->id]);
    }


    public function generateToken()
    {
        $this->tokens()->delete();
        $token = $this->createToken('fb');
        return $token->plainTextToken;
    }
}
