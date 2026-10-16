<?php

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
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
        $table->string('razorpay_order_id')->unique();
        $table->string('razorpay_payment_id')->nullable()->unique();
        $table->decimal('amount', 10, 2);
        $table->enum('status', ['created', 'paid', 'failed'])->default('created');
        $table->timestamp('paid_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
