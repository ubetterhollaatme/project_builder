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
        Schema::create('data_producer_nodes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('name')
                ->default('Data Producer Node');
            $table->string('email')
                ->default('ubetterhollaatme@yandex.ru')
                ->unique();
            $table->bigInteger('phone')
                ->unique();
            $table->longText('desc')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_producer_nodes');
    }
};
