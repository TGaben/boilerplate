<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documentations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('content');
            $table->string('category');
            $table->string('path');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('category');
            $table->index('slug');
            $table->unique(['category', 'slug']); // Unique constraint on category + slug combination
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentations');
    }
};
