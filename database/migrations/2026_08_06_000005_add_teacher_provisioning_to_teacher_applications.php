<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_applications', function (Blueprint $table): void {
            $table->string('teacher_provision_status', 30)->default('not_provisioned')->after('status')->index();
            $table->string('provisioned_user_role', 30)->nullable()->after('teacher_provision_status');
            $table->foreignId('provisioned_by')->nullable()->after('provisioned_user_role')->constrained('users')->nullOnDelete();
            $table->timestamp('provisioned_at')->nullable()->after('provisioned_by')->index();
            $table->timestamp('teacher_suspended_at')->nullable()->after('provisioned_at')->index();
            $table->timestamp('teacher_revoked_at')->nullable()->after('teacher_suspended_at')->index();
            $table->text('provisioning_note')->nullable()->after('teacher_revoked_at');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_applications', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('provisioned_by');
            $table->dropColumn([
                'teacher_provision_status',
                'provisioned_user_role',
                'provisioned_at',
                'teacher_suspended_at',
                'teacher_revoked_at',
                'provisioning_note',
            ]);
        });
    }
};
