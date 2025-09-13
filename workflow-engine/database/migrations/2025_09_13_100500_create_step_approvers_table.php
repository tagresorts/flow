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
        Schema::create('step_approvers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('step_id')->constrained('workflow_steps')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('cascade');
            $table->string('approval_type'); // e.g., 'user', 'role', 'manager'
            $table->foreignId('escalation_step_id')->nullable()->constrained('workflow_steps')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('step_approvers');
    }
};
