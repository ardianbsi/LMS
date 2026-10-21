<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fase 4: pertemuan, sesi presensi, presensi, koreksi presensi.
     * - meetings: pertemuan bernomor dalam kelas (nomor unik per kelas).
     * - attendance_sessions: sesi presensi per pertemuan (buka/tutup).
     * - attendances: status kehadiran mahasiswa (unik per mahasiswa per sesi).
     * - attendance_corrections: audit koreksi status presensi (actor, waktu, alasan).
     */
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_class_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('meeting_number');
            $table->string('title')->nullable();
            $table->date('meeting_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->text('topic')->nullable();
            $table->text('summary')->nullable();
            $table->timestamps();

            $table->unique(['course_class_id', 'meeting_number']);
            $table->index('meeting_date');
        });

        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->string('status', 16)->default('belum');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('meeting_id');
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('status', 16)->default('present');
            $table->timestamp('checked_in_at')->nullable();
            $table->string('method', 32)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['attendance_session_id', 'student_id']);
        });

        Schema::create('attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $table->string('old_status', 16);
            $table->string('new_status', 16);
            $table->text('reason');
            $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->index('attendance_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_corrections');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('attendance_sessions');
        Schema::dropIfExists('meetings');
    }
};
