<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('authorized_persons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('photo_path')->nullable();
            $table->json('face_descriptor');
            $table->string('access_level')->default('standard');
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('authorized_persons');
    }
};