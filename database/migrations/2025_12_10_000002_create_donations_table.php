<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained();
            $table->foreignId('donor_id')->constrained();
            $table->string('payment_method')->default('card');
            $table->string('check_number')->nullable();
            $table->integer('amount');
            $table->string('status')->default('pending')->index();

            // Tax filing info
            $table->integer('filing_year');
            $table->string('filing_status');
            $table->string('qco')->nullable()->comment('Qualified Charitable Organization code');

            // Snapshot for historical records
            $table->string('school_name_snapshot')->nullable();

            // Tax professional info
            $table->string('tax_professional_name')->nullable();
            $table->string('tax_professional_phone')->nullable();
            $table->string('tax_professional_email')->nullable();

            // Receipt tracking
            $table->timestamp('receipt_sent_at')->nullable();

            $table->foreignId('creator_id')->nullable()->constrained('users');
            $table->foreignId('updater_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('filing_year');
            $table->index('created_at');
        });
    }
};
