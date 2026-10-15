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
    Schema::create('invoices', function (Blueprint $table) {
        $table->id();
        $table->foreignId('lease_id')->constrained()->cascadeOnDelete();
        $table->date('period');
        $table->decimal('amount', 10, 2);
        $table->decimal('late_fee', 10, 2)->default(0);
        $table->date('due_date');
        $table->enum('status', ['pending', 'paid', 'overdue', 'void'])->default('pending');
        $table->timestamp('paid_at')->nullable();
        $table->timestamps();

        $table->unique(['lease_id', 'period']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
