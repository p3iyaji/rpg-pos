<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('expense_category_id')->nullable();
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->foreignId('user_id')->constrained();

            $table->timestamps();

            $table->foreign('expense_category_id')->references('id')->on('expense_categories');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
