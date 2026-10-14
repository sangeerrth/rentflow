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
        Schema::create('leases', function (Blueprint $table) {
        $table->id();
        $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
        $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
        $table->decimal('rent_amount', 10, 2);
        $table->decimal('security_deposit', 10, 2)->default(0);
        $table->date('start_date');
        $table->date('end_date');
        $table->unsignedTinyInteger('rent_due_day')->default(1);
        $table->unsignedTinyInteger('grace_days')->default(5);
        $table->decimal('late_fee_amount', 10, 2)->default(0);
        $table->enum('status', ['active', 'ended', 'terminated'])->default('active');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leases');
    }
};
