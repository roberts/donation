<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->morphs('addressable');
            $table->string('type')->default('mailing')->index(); // mailing, billing
            $table->string('street');
            $table->string('street_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('US');
            $table->foreignId('creator_id')->nullable()->constrained('users');
            $table->foreignId('updater_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
