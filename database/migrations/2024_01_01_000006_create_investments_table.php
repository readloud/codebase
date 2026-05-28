<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->decimal('amount', 15, 2);
            $table->decimal('profit_rate', 5, 2)->default(10);
            $table->timestamp('start_date');
            $table->timestamp('end_date')->nullable();
            $table->enum('status', ['active', 'completed', 'withdrawn'])->default('active');
            $table->timestamps();
        });

        Schema::create('profit_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')->constrained();
            $table->decimal('amount', 15, 2);
            $table->timestamp('calculated_at');
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('profit_shares');
        Schema::dropIfExists('investments');
    }
};