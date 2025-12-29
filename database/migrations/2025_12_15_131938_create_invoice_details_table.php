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
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id();
            // Kunci asing ke Invoice (wajib)
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            // Kunci asing ke SparePart (bisa null jika itemnya adalah Jasa)
            $table->foreignId('spare_part_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('description')->nullable(); // Deskripsi item (digunakan jika spare_part_id null)
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2); // Harga Satuan * Kuantitas
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_details');
    }
};