<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_number')->unique();  // e.g. RES-2026-00001
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->integer('queue_position')->default(1);
            $table->enum('status', ['pending', 'available', 'fulfilled', 'cancelled', 'expired'])->default('pending');
            $table->date('reserved_date');
            $table->date('expiry_date');
            $table->date('notification_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('reservations'); }
};
