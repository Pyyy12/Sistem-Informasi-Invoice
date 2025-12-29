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
            // Kunci asing ke Invoice (transaksi mana yang dibayar)
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            
            $table->date('payment_date');
            $table->decimal('amount', 10, 2); // Jumlah pembayaran
            
            // Metode Pembayaran
            $table->enum('method', ['CASH', 'TRANSFER', 'DEBIT', 'CREDIT'])->default('CASH');
            $table->string('reference')->nullable(); // Contoh: Nomor Transaksi Bank, Nomor Kartu
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