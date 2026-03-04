<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->integer('ibe_id')->nullable()->unique()->comment('Internal IBE school identifier');
            $table->string('name');
            $table->string('type', 16)->default('private');
            $table->foreignId('creator_id')->nullable()->constrained('users');
            $table->foreignId('updater_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('name');
        });
    }
};
