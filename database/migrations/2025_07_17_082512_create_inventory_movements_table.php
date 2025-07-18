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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->string('product_sku');
            $table->integer('quantity');
            $table->string('movement_type'); // 'purchase', 'sale', 'adjustment', 'return', etc.
            $table->foreignId('reference_id')->nullable(); // polymorphic relation
            $table->string('reference_type')->nullable(); // e.g. 'App\\Models\\PurchaseOrder'
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->index('product_sku');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
