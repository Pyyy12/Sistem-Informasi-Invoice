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
            // Relasi ke Customer
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            // Relasi ke Vehicle (bisa null jika invoice bukan untuk perbaikan/servis)
            $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->decimal('total_amount', 10, 2)->default(0);
            
            // Status Faktur
            $table->enum('status', ['DRAFT', 'ISSUED', 'PAID', 'CANCELLED'])->default('ISSUED');
            
            $table->text('notes')->nullable();
            $table->timestamps();
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