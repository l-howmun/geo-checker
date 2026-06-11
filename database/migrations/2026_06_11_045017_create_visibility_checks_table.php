<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visibility_checks', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->string('prompt');
            $table->string('engine');
            $table->boolean('mentioned')->default(false);
            $table->text('snippet')->nullable();
            $table->string('sentiment')->nullable(); // positive, neutral, negative
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index(['brand', 'prompt', 'engine']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visibility_checks');
    }
};
