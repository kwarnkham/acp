<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
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

    public function test_list_items(): void
    {
        Event::fake();
        $item = Item::factory()->create();
        $item2 = Item::factory()->create();

        $reponse = $this->getJson('/api/items');

        $reponse->assertOk()->assertJson([
            'data' => true
        ])->assertJsonPath('data.data', [
            $item->fresh()->toArray(),
            $item2->fresh()->toArray()
        ]);

        $reponse->assertOk();

        $this->assertArrayHasKey('data', $reponse->json());
    }

    public function test_find_item(): void
    {
        Event::fake();
        $item = Item::factory()->create();
        $reponse = $this->getJson("/api/items/$item->id");
        $reponse->assertOk()->assertJsonPath('item', $item->fresh()->toArray());
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_update_item(): void
    {
        Event::fake();
        $item = Item::factory()->create();
        $name = fake()->name();
        $price = fake()->numberBetween(1000, 100000);
        $reponse = $this->putJson("/api/items/$item->id", [
            'name' => $name,
            'price' => $price
        ]);
        $reponse->assertOk()->assertJsonPath('item.name', $name);
        $reponse->assertOk()->assertJsonPath('item.price', $price);
        $this->assertDatabaseCount('tickets', 0);
    }
}
