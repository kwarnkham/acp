<?php

namespace Tests\Feature;

use App\Models\Item;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ItemTest extends TestCase
{
    public function test_create_item(): void
    {
        $maxTickets = $this->faker->numberBetween(10, 100);
        $pricePerTicket = $this->faker->numberBetween(1000, 2000);
        $reponse = $this->postJson('/api/items', [
            'name' => $this->faker->lastName(),
            'max_tickets' => $maxTickets,
            'price_per_ticket' => $pricePerTicket,
            'price' => $this->faker->numberBetween(1000, 2000)
        ]);

        $reponse->assertCreated();
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
        $name = $this->faker->name();
        $price = $this->faker->numberBetween(1000, 100000);
        $reponse = $this->putJson("/api/items/$item->id", [
            'name' => $name,
            'price' => $price
        ]);
        $reponse->assertOk()->assertJsonPath('item.name', $name);
        $reponse->assertOk()->assertJsonPath('item.price', $price);
        $this->assertDatabaseCount('tickets', 0);
    }
}
