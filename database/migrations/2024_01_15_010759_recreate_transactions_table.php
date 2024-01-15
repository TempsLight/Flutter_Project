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
        // Drop the transactions table if it already exists
        Schema::dropIfExists('transactions');

        // Then create the transactions table
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number', 191);
            $table->foreign('phone_number')->references('phone_number')->on('users')->onDelete('cascade');
            $table->decimal('amount', 8, 2);
            $table->enum('type', ['deposit', 'transfer']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
