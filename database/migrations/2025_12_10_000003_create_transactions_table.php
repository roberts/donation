<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donation_id')->nullable()->constrained();
            $table->string('payment_intent_id');
            $table->integer('amount');
            $table->string('status')->default('pending');
            $table->boolean('livemode')->default(true);
            $table->json('payload')->nullable()->comment('Raw Stripe webhook payload');
            $table->foreignId('creator_id')->nullable()->constrained('users');
            $table->foreignId('updater_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('payment_intent_id');
            $table->index('status');
            $table->index('created_at');
        });
    }
};
