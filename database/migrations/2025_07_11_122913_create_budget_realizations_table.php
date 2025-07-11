<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_realizations', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke item anggaran yang direncanakan
            $table->foreignId('budget_item_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2); // Jumlah pengeluaran
            $table->date('transaction_date'); // Tanggal transaksi
            $table->text('description')->nullable();
            $table->string('receipt_path')->nullable(); // Path untuk bukti/nota
            $table->foreignId('user_id')->constrained(); // Siapa yang mencatat
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_realizations');
    }
};
