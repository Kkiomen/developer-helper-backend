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
        Schema::create('documentation_file_contents', function (Blueprint $table) {
            $table->id();
            $table->text('filename')->nullable();
            $table->text('md5')->nullable();
            $table->text('embedded')->nullable();
            $table->text('parse_content')->nullable();
            $table->string('header')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentation_file_contents');
    }
};
