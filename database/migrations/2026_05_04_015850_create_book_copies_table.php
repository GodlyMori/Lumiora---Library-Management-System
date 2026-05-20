<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('book_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->string('copy_number');  // e.g. COPY-001
            $table->enum('condition', ['new', 'good', 'fair', 'poor'])->default('good');
            $table->enum('status', ['available', 'borrowed', 'reserved', 'damaged', 'lost'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('book_copies'); }
};
