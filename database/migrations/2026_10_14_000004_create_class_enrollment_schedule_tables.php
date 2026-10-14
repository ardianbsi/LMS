<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fase 3: kelas, dosen pengampu, enrollment, jadwal.
     * - course_classes: MK pada semester + kode kelas (cth: A, B).
     * - class_lecturers: pivot dosen pengampu (unik per kelas+dosen).
     * - enrollments: pivot mahasiswa (unik per kelas+mahasiswa).
     * - class_schedules: jadwal mingguan per kelas (hari + jam + ruang opsional + rentang tanggal opsional).
     */
    public function up(): void
    {
        Schema::create('course_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->foreignId('semester_id')->constrained()->restrictOnDelete();
            $table->string('class_code', 16);
            $table->string('name', 255)->nullable();
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->string('status', 32)->default('aktif');
            $table->timestamps();

            $table->unique(['course_id', 'semester_id', 'class_code']);
            $table->index('semester_id');
        });

        Schema::create('class_lecturers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lecturer_id')->constrained()->restrictOnDelete();
            $table->string('role', 32)->default('pengampu');
            $table->timestamps();

            $table->unique(['course_class_id', 'lecturer_id']);
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->string('status', 32)->default('aktif');
            $table->timestamps();

            $table->unique(['course_class_id', 'student_id']);
        });

        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('day_of_week', 16);
            $table->time('start_time');
            $table->time('end_time');
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['day_of_week', 'start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_schedules');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('class_lecturers');
        Schema::dropIfExists('course_classes');
    }
};
