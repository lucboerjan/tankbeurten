<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tankbeurts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('voertuigID')->default(null)->nullable();
            $table->date('datum')->default(null)->nullable();
            $table->unsignedBigInteger('kmstand')->default(null)->nullable();
            $table->decimal('volume',$precision=10, $scale=2)->default(0.00);
            $table->decimal('bedrag',$precision=10, $scale=2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tankbeurts');
    }
};
