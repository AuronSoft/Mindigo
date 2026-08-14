<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_session_attempts', function (Blueprint $table): void {
            $table->string('proctor_session_key', 64)->nullable()->index()->after('security_events');
            $table->char('initial_ip_hash', 64)->nullable()->after('proctor_session_key');
            $table->char('last_ip_hash', 64)->nullable()->after('initial_ip_hash');
            $table->char('initial_device_hash', 64)->nullable()->after('last_ip_hash');
            $table->char('last_device_hash', 64)->nullable()->after('initial_device_hash');
            $table->unsignedSmallInteger('risk_score')->default(0)->after('last_device_hash');
            $table->string('risk_level', 16)->default('low')->index()->after('risk_score');
            $table->foreignId('current_question_id')->nullable()->after('risk_level')->constrained('exam_template_questions')->nullOnDelete();
            $table->timestamp('camera_consent_at')->nullable()->after('current_question_id');
            $table->timestamp('camera_consent_declined_at')->nullable()->after('camera_consent_at');
            $table->foreignId('terminated_by')->nullable()->after('camera_consent_declined_at')->constrained('users')->nullOnDelete();
            $table->timestamp('terminated_at')->nullable()->after('terminated_by');
            $table->text('termination_reason')->nullable()->after('terminated_at');
        });

        Schema::create('exam_proctor_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_session_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 48)->index();
            $table->string('source', 16);
            $table->string('risk_level', 16)->default('low')->index();
            $table->unsignedSmallInteger('risk_weight')->default(0);
            $table->string('session_key', 64)->nullable();
            $table->char('ip_hash', 64)->nullable();
            $table->char('device_hash', 64)->nullable();
            $table->string('evidence_path')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();

            $table->index(['exam_session_attempt_id', 'occurred_at'], 'exam_proctor_attempt_time_index');
            $table->index(['exam_session_id', 'risk_level'], 'exam_proctor_session_risk_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_proctor_events');

        Schema::table('exam_session_attempts', function (Blueprint $table): void {
            $table->dropIndex(['proctor_session_key']);
            $table->dropIndex(['risk_level']);
            $table->dropConstrainedForeignId('current_question_id');
            $table->dropConstrainedForeignId('terminated_by');
            $table->dropColumn([
                'proctor_session_key', 'initial_ip_hash', 'last_ip_hash', 'initial_device_hash',
                'last_device_hash', 'risk_score', 'risk_level', 'camera_consent_at',
                'camera_consent_declined_at', 'terminated_at', 'termination_reason',
            ]);
        });
    }
};
