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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            // Kunci asing ke tabel customers
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('plate_number')->unique(); // Nomor Polisi (Plat)
            $table->string('make'); // Merek (Contoh: Honda, Toyota)
            $table->string('model'); // Model (Contoh: Civic, Avanza)
            $table->string('year')->nullable(); // Tahun pembuatan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};