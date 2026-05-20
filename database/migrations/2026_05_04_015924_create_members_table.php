<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('membership_id')->unique();    // e.g. MEM-2026-0001
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->enum('member_type', ['student', 'faculty', 'staff', 'public'])->default('public');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->date('membership_start_date');
            $table->date('membership_expiry_date');
            $table->integer('max_books')->default(5);
            $table->integer('borrowed_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('members'); }
};
