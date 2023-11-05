<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->actingAs(User::where('name', 'admin')->first());
    }

    public function test_create_item(): void
    {
        $maxTickets = fake()->numberBetween(10, 100);
        $pricePerTicket = fake()->numberBetween(1000, 2000);
        $reponse = $this->postJson('/api/items', [
            'name' => fake()->lastName(),
            'max_tickets' => $maxTickets,
            'price_per_ticket' => $pricePerTicket,
            'price' => fake()->numberBetween(1000, 2000)
        ]);

        $reponse->assertCreated();
    }

    public function test_create_item_also_create_tickets(): void
    {
        $maxTickets = fake()->numberBetween(10, 100);
        $pricePerTicket = fake()->numberBetween(1000, 2000);
        $reponse = $this->postJson('/api/items', [
            'name' => fake()->lastName(),
            'max_tickets' => $maxTickets,
            'price_per_ticket' => $pricePerTicket,
            'price' => fake()->numberBetween(1000, 2000)
        ]);

        $reponse->assertCreated();
        $this->assertDatabaseCount('tickets', $maxTickets);

        Ticket::query()->get()->each(function ($ticket) use ($reponse) {
            $this->assertEquals($ticket->item_id, $reponse->json()['item']['id']);
        });
    }
}
