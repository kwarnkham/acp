<?php

namespace Tests\Feature;

use App\Models\Item;
use Tests\TestCase;

class ItemTest extends TestCase
{
    public function test_create_item(): void
    {
        $item = Item::factory()->make()->toArray();
        $reponse = $this->postJson('/api/items', $item);
        $reponse->assertCreated();
    }
}
