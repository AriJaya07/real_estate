<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_submissions', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_property_id')->nullable()->constrained('properties')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('property_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('published_property_id');
            $table->dropColumn('published_at');
        });
    }
};
