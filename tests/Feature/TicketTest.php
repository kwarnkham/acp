<?php

namespace Tests\Feature;

use App\Enums\ResponseStatus;
use App\Enums\TicketStatus;
use App\Models\Item;
use App\Models\Preference;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class TicketTest extends TestCase
{
    private $user;
    public function setUp(): void
    {
        parent::setUp();
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }
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

    public function test_book_a_ticket()
    {
        $item = Item::factory()->state(['max_tickets' => 10])->create();
        $ticket = $item->tickets()->inRandomOrder()->first();
        $response = $this->putJson("api/tickets/$ticket->id", [
            'status' => TicketStatus::BOOKED->value
        ]);

        $response->assertOk()->assertJsonPath('ticket.status', TicketStatus::BOOKED->value);
    }


    public function test_a_booked_ticket_will_expires_and_status_will_change_back_to_available()
    {
        $item = Item::factory()->state(['max_tickets' => 10])->create();
        $ticket = $item->tickets()->inRandomOrder()->first();
        $response = $this->putJson("api/tickets/$ticket->id", [
            'status' => TicketStatus::BOOKED->value
        ]);

        $response->assertOk();
        $this->assertDatabaseCount('ticket_user', 1);
        $expiresAt = new Carbon($ticket->users()->first()->pivot->expires_at);
        $this->assertNotNull($expiresAt);

        $this->assertTrue(now()->lessThan($expiresAt));

        $this->travel(Preference::first()->ticket_expiration)->minutes();

        $this->assertTrue(now()->greaterThanOrEqualTo($expiresAt));

        $this->assertEquals($ticket->status, TicketStatus::AVAILABLE->value);
    }

    public function test_can_only_book_an_available_ticket()
    {
        $item = Item::factory()->state(['max_tickets' => 10])->create();
        $ticket = $item->tickets()->inRandomOrder()->first();
        $this->assertEquals($ticket->status, TicketStatus::AVAILABLE->value);
        $response = $this->putJson("api/tickets/$ticket->id", [
            'status' => TicketStatus::BOOKED->value
        ]);
        $ticket->refresh();
        $this->assertEquals($ticket->status, TicketStatus::BOOKED->value);
        $response->assertOk();

        $this->putJson("api/tickets/$ticket->id", [
            'status' => TicketStatus::BOOKED->value
        ])->assertBadRequest();

        $this->assertTrue($this->user->payTicket($ticket, UploadedFile::fake()->image('foo.png')));

        $this->putJson("api/tickets/$ticket->id", [
            'status' => TicketStatus::BOOKED->value
        ])->assertBadRequest();

        $this->assertTrue($this->user->confirmPaid($ticket));

        $this->putJson("api/tickets/$ticket->id", [
            'status' => TicketStatus::BOOKED->value
        ])->assertBadRequest();
    }

    public function test_can_only_pay_an_booked_ticket()
    {
        $item = Item::factory()->state(['max_tickets' => 10])->create();
        $ticket = $item->tickets()->inRandomOrder()->first();
        $image = UploadedFile::fake()->image($this->faker->name() . '.png');
        $this->assertEquals($ticket->status, TicketStatus::AVAILABLE->value);

        $this->putJson("api/tickets/$ticket->id", [
            'status' => TicketStatus::PAID->value,
            'screenshot' => $image
        ])->assertBadRequest();

        $this->assertTrue($this->user->bookTicket($ticket));

        $this->putJson("api/tickets/$ticket->id", [
            'status' => TicketStatus::PAID->value,
            'screenshot' => $image
        ])->assertOk();

        $this->assertEquals($ticket->fresh()->status, TicketStatus::PAID->value);

        $this->putJson("api/tickets/$ticket->id", [
            'status' => TicketStatus::PAID->value,
            'screenshot' => $image
        ])->assertBadRequest();

        $this->assertTrue($this->user->confirmPaid($ticket));

        $this->putJson("api/tickets/$ticket->id", [
            'status' => TicketStatus::PAID->value,
            'screenshot' => $image
        ])->assertBadRequest();
    }

    public function test_can_only_confirm_paid_a_paid_ticket()
    {
        $item = Item::factory()->state(['max_tickets' => 10])->create();
        $ticket = $item->tickets()->inRandomOrder()->first();
        $admin = User::whereRelation('roles', 'role_id', '=', Role::query()->where('name', 'admin')->first()->id)->first();

        $this->assertEquals($ticket->status, TicketStatus::AVAILABLE->value);
        $this->actingAs($admin)->putJson("api/tickets/$ticket->id", [
            'status' => TicketStatus::CONFIRMED_PAID->value
        ])->assertBadRequest();

        $this->assertTrue($this->user->bookTicket($ticket));
        $this->actingAs($admin)->putJson("api/tickets/$ticket->id", [
            'status' => TicketStatus::CONFIRMED_PAID->value
        ])->assertBadRequest();

        $this->assertTrue($this->user->payTicket($ticket, UploadedFile::fake()->image('foo.png')));

        $this->actingAs($admin)->putJson("api/tickets/$ticket->id", [
            'status' => TicketStatus::CONFIRMED_PAID->value
        ])->assertOk();

        $this->assertEquals($ticket->fresh()->status, TicketStatus::CONFIRMED_PAID->value);

        $this->actingAs($admin)->putJson("api/tickets/$ticket->id", [
            'status' => TicketStatus::CONFIRMED_PAID->value
        ])->assertBadRequest();
    }

    public function test_only_admin_can_confirm_payment()
    {
        $item = Item::factory()->state(['max_tickets' => 10])->create();
        $ticket = $item->tickets()->inRandomOrder()->first();
        $admin = User::whereRelation('roles', 'role_id', '=', Role::query()->where('name', 'admin')->first()->id)->first();
        $this->assertTrue($this->user->bookTicket($ticket));
        $this->assertTrue($this->user->payTicket($ticket, UploadedFile::fake()->image('foo.png')));

        $this->putJson("api/tickets/$ticket->id", [
            'status' => TicketStatus::CONFIRMED_PAID->value
        ])->assertStatus(ResponseStatus::UNAUTHORIZED->value);

        $this->actingAs($admin)->putJson("api/tickets/$ticket->id", [
            'status' => TicketStatus::CONFIRMED_PAID->value
        ])->assertOk();

        $this->assertEquals($ticket->fresh()->status, TicketStatus::CONFIRMED_PAID->value);
    }
}
