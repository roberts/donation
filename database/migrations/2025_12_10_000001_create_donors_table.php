<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('title')->nullable();
            $table->string('spouse_title')->nullable();
            $table->string('spouse_first_name')->nullable();
            $table->string('spouse_last_name')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('creator_id')->nullable()->constrained('users');
            $table->foreignId('updater_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('last_name');
        });
    }
};
