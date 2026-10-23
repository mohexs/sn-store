<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('fulfillment_status', 20)->default('picking')->index();
            $table->string('shipping_carrier', 100)->nullable();
            $table->string('tracking_number', 100)->nullable();
            $table->timestamp('shipped_at')->nullable();
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedInteger('processed_quantity')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('order_items', fn (Blueprint $table) => $table->dropColumn('processed_quantity'));
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['fulfillment_status']);
            $table->dropColumn(['fulfillment_status', 'shipping_carrier', 'tracking_number', 'shipped_at']);
        });
    }
};
