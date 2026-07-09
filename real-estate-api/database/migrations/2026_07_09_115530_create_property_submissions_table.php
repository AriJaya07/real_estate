<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('owner_name');
            $table->string('phone');
            $table->string('email');
            $table->string('address');
            $table->decimal('listing_price', 14, 2);
            $table->string('status');
            $table->text('description');
            $table->text('notes')->nullable();
            $table->boolean('publish_ready')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_submissions');
    }
};
