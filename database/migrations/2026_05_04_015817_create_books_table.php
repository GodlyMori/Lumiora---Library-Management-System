<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('book_categories')->nullOnDelete();
            $table->string('title');
            $table->string('author');
            $table->string('isbn')->unique()->nullable();
            $table->string('publisher')->nullable();
            $table->integer('published_year')->nullable();
            $table->text('description')->nullable();
            $table->string('language')->default('English');
            $table->integer('pages')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('location')->nullable();
            $table->integer('total_copies')->default(1);
            $table->integer('available_copies')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('books'); }
};
