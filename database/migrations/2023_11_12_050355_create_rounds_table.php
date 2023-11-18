<?php

use App\Enums\RoundStatus;
use App\Models\Ticket;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained();
            $table->foreignIdFor(Ticket::class, 'ticket_id')->nullable();
            $table->tinyInteger('status')->default(RoundStatus::ONGOING->value);
            $table->unsignedInteger('max_tickets');
            $table->unsignedBigInteger('price_per_ticket');
            $table->unsignedBigInteger('price');
            $table->string('note')->nullable();
            $table->unsignedInteger('expires_in')->default(60);
            $table->timestamps();
            $table->integer('code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rounds');
    }
};
