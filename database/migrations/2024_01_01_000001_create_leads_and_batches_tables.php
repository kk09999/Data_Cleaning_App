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
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->integer('sheet_count')->default(1);
            $table->integer('record_count')->default(0);
            $table->integer('unique_count')->default(0);
            $table->integer('duplicate_count')->default(0);
            $table->timestamps();
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_batch_id')->nullable()->constrained('import_batches')->onDelete('cascade');
            $table->string('sheet_name')->default('Sheet1');
            $table->string('date')->nullable();
            $table->string('month')->nullable();
            $table->string('name')->nullable();
            $table->string('mob')->nullable()->index(); // Indexed for phone number duplicate validation
            $table->string('email')->nullable();
            $table->string('raw_course')->nullable();
            $table->string('major_category')->default('Other')->index();
            $table->string('source')->nullable()->default('Direct/Organic')->index(); // Lead source field
            $table->boolean('is_duplicate')->default(false)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
        Schema::dropIfExists('import_batches');
    }
};
