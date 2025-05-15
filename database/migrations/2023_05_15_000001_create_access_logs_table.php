<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('authorized_person_id')->nullable()->constrained()->nullOnDelete();
            $table->string('person_name')->nullable();
            $table->enum('status', ['authorized', 'unauthorized', 'unknown']);
            $table->string('photo_path')->nullable();
            $table->timestamp('access_time');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('access_logs');
    }
};