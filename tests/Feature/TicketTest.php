<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Models\Item;
use App\Models\Ticket;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class TicketTest extends TestCase
{
    public function test_create_item_also_create_tickets(): void
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
        $this->assertDatabaseCount('tickets', $maxTickets);

        Ticket::query()->get()->each(function ($ticket) use ($reponse) {
            $this->assertEquals($ticket->item_id, $reponse->json()['item']['id']);
        });
    }

    public function test_list_tickets()
    {
        $maxTickets = $this->faker->numberBetween(10, 100);
        $item = Item::factory()->state(['max_tickets' => $maxTickets])->create();
        $perPage =  (int)floor($maxTickets / 10);
        $reponse = $this->getJson("/api/tickets?per_page=$perPage&item_id=$item->id");
        $reponse->assertOk()->assertJson(
            fn (AssertableJson $json) =>
            $json
                ->has('data')
                ->where('data.current_page', 1)
                ->where('data.total', $maxTickets)
                ->where('data.per_page', $perPage)
                ->has('data.data', $perPage)
                ->has(
                    'data.data.0',
                    fn (AssertableJson $json) =>
                    $json->where('id', $item->tickets()->first()->id)
                        ->where('code', 0)
                        ->where('item_id', $item->id)
                        ->where('status', TicketStatus::AVAILABLE->value)
                        ->where('user_id', null)
                        ->etc()
                )
        );
    }

    public function test_find_a_ticket()
    {
        $item = Item::factory()->create();
        $needle = $item->tickets()->inRandomOrder()->first();
        $response = $this->getJson("/api/tickets/$needle->id");
        $response->assertOk()->assertJson(
            fn (AssertableJson $json) => $json->has(
                'ticket',
                fn (AssertableJson $json) => $json
                    ->where('id', $needle->id)
                    ->where('code', $needle->code)
                    ->where('status', $needle->status)
                    ->where('updated_at', $needle->updated_at->toJson())
                    ->where('created_at', $needle->created_at->toJson())
                    ->where('item_id', $item->id)
                    ->where('user_id', $needle->user_id)
                    ->has('item')

            )
        );
    }
}
