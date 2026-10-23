<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 64)->unique();
            $table->string('customer_name')->nullable();
            $table->date('sale_date');
            $table->string('status', 20)->default('pending');
            $table->timestamps();
            $table->index(['status', 'sale_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
