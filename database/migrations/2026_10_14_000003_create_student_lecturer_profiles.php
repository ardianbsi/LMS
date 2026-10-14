<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * students dan lecturers adalah profil terkait users.
     * Nama/email TIDAK diduplikasi — diambil dari relasi user.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('study_program_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('nim', 32)->unique();
            $table->unsignedSmallInteger('entry_year');
            $table->string('phone', 32)->nullable();
            $table->text('address')->nullable();
            $table->string('status', 32)->default('aktif');
            $table->timestamps();
        });

        Schema::create('lecturers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('study_program_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('nip', 32)->unique();
            $table->string('academic_title', 128)->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('status', 32)->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecturers');
        Schema::dropIfExists('students');
    }
};
