<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('subtotal_minor')->default(0);
            $table->unsignedBigInteger('discount_minor')->default(0);
            $table->unsignedBigInteger('total_minor')->default(0);
            $table->foreignId('promotion_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payment_method', 30)->default('bank_transfer');
            $table->string('payment_status', 20)->default('pending')->index();
        });

        DB::table('orders')
            ->where('status', 'paid')
            ->update(['payment_status' => 'paid']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['promotion_id']);
            $table->dropIndex(['payment_status']);
            $table->dropColumn([
                'subtotal_minor',
                'discount_minor',
                'total_minor',
                'promotion_id',
                'payment_method',
                'payment_status',
            ]);
        });
    }
};
